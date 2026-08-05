#!/usr/bin/env python3
"""Build lab catalog JSON from lab-tests/*.xlsx exports.

Sources:
  - lab-tests/1-200.xlsx … 801-873.xlsx  → test id, test code, price
  - lab-tests/TestParameter62513.xlsx      → test name, category, parameters

Output:
  database/seeders/data/lab_catalog.json

Run:
  python scripts/build_lab_catalog_json.py
"""

from __future__ import annotations

import json
import re
from pathlib import Path

ROOT = Path(__file__).resolve().parents[1]
LAB_TESTS_DIR = ROOT / "lab-tests"
OUT = ROOT / "database" / "seeders" / "data" / "lab_catalog.json"

NS = {"m": "http://schemas.openxmlformats.org/spreadsheetml/2006/main"}


def col_index(col: str) -> int:
    idx = 0
    for ch in col:
        idx = idx * 26 + (ord(ch) - ord("A") + 1)
    return idx - 1


def read_xlsx_rows(path: Path, sheet_index: int = 0) -> list[list[str]]:
    import xml.etree.ElementTree as ET
    import zipfile

    with zipfile.ZipFile(path) as zf:
        workbook = ET.fromstring(zf.read("xl/workbook.xml"))
        sheets = workbook.findall(".//m:sheet", NS)
        rels = ET.fromstring(zf.read("xl/_rels/workbook.xml.rels"))
        rid_map = {rel.get("Id"): rel.get("Target") for rel in rels}

        shared: list[str] = []
        if "xl/sharedStrings.xml" in zf.namelist():
            ss_root = ET.fromstring(zf.read("xl/sharedStrings.xml"))
            for si in ss_root.findall("m:si", NS):
                texts = [node.text or "" for node in si.findall(".//m:t", NS)]
                shared.append("".join(texts))

        rel_id = sheets[sheet_index].get(
            "{http://schemas.openxmlformats.org/officeDocument/2006/relationships}id"
        )
        sheet_path = "xl/" + rid_map[rel_id].lstrip("/")
        sheet_root = ET.fromstring(zf.read(sheet_path))

        rows: list[list[str]] = []
        for row_el in sheet_root.findall("m:sheetData/m:row", NS):
            cell_map: dict[int, str] = {}
            for cell in row_el.findall("m:c", NS):
                ref = cell.get("r", "")
                col = "".join(ch for ch in ref if ch.isalpha())
                idx = col_index(col) if col else len(cell_map)

                inline = cell.find("m:is", NS)
                value_el = cell.find("m:v", NS)
                value = ""

                if inline is not None:
                    value = "".join(
                        (node.text or "") for node in inline.findall(".//m:t", NS)
                    )
                elif value_el is not None:
                    value = value_el.text or ""
                    if cell.get("t") == "s":
                        value = shared[int(value)]
                else:
                    continue

                cell_map[idx] = value

            if not cell_map:
                rows.append([])
                continue

            max_idx = max(cell_map)
            row_vals = [""] * (max_idx + 1)
            for idx, val in cell_map.items():
                row_vals[idx] = val
            rows.append(row_vals)

        return rows


def to_float(value: str | None) -> float | None:
    if value is None:
        return None
    text = str(value).strip()
    if text == "":
        return None
    try:
        return float(text)
    except ValueError:
        return None


def format_range_value(value: float | None) -> str | None:
    if value is None:
        return None
    if value == int(value):
        return str(int(value))
    return str(value)


def normalize_category(value: str | None) -> str:
    text = (value or "").strip()
    return text if text else "General Pathology"


def normalize_head(category: str) -> str:
    mapping = {
        "Chemical Pathology": "Biochemistry",
        "Special Chemistry": "Special Chemistry",
        "Hematology": "Hematology",
        "Microbiology": "Microbiology",
        "Molecular Biology": "Molecular Biology",
        "Histopathology": "Histopathology",
        "Blood Bank": "Blood Bank",
        "Radiology": "Radiology",
    }
    return mapping.get(category, category)


def slug_key(text: str) -> str:
    slug = re.sub(r"[^a-z0-9]+", "_", text.lower()).strip("_")
    return slug or "parameter"


def load_price_rows() -> dict[str, dict]:
    prices: dict[str, dict] = {}

    for path in sorted(LAB_TESTS_DIR.glob("[0-9]*.xlsx")):
        for row in read_xlsx_rows(path)[1:]:
            if not row or not str(row[0]).strip():
                continue

            test_id = str(row[0]).strip()
            code = str(row[1]).strip() if len(row) > 1 else ""
            price = to_float(row[6] if len(row) > 6 else None) or 0.0

            prices[test_id] = {
                "id": int(test_id),
                "test_code": code,
                "price": price,
            }

    return prices


def load_parameter_rows() -> tuple[dict[str, dict], dict[str, list[dict]]]:
    path = LAB_TESTS_DIR / "TestParameter62513.xlsx"
    rows = read_xlsx_rows(path)
    tests: dict[str, dict] = {}
    particulars_by_test: dict[str, list[dict]] = {}

    for row in rows[1:]:
        if not row or not str(row[0]).strip():
            continue

        test_id = str(row[0]).strip()
        category = normalize_category(row[1] if len(row) > 1 else "")
        test_code = str(row[2]).strip() if len(row) > 2 else ""
        test_name = str(row[3]).strip() if len(row) > 3 else ""

        if test_id not in tests:
            tests[test_id] = {
                "id": int(test_id),
                "name": test_name,
                "test_code": test_code,
                "category": category,
                "head": normalize_head(category),
            }

        param_ex_id = str(row[4]).strip() if len(row) > 4 else ""
        param_name = str(row[5]).strip() if len(row) > 5 else ""
        patient_type = str(row[6]).strip() if len(row) > 6 else ""
        critical_min = to_float(row[7] if len(row) > 7 else None)
        min_val = to_float(row[8] if len(row) > 8 else None)
        max_val = to_float(row[9] if len(row) > 9 else None)
        critical_max = to_float(row[10] if len(row) > 10 else None)
        reference_words = str(row[11]).strip() if len(row) > 11 else ""
        unit = str(row[12]).strip() if len(row) > 12 else ""
        interpretation = str(row[13]).strip() if len(row) > 13 else ""

        if param_name == "":
            continue

        particulars_by_test.setdefault(test_id, []).append(
            {
                "external_id": int(param_ex_id) if param_ex_id.isdigit() else None,
                "name": param_name,
                "patient_type": patient_type or None,
                "unit": unit or None,
                "normal_range_min": format_range_value(min_val),
                "normal_range_max": format_range_value(max_val),
                "critical_range_min": format_range_value(critical_min),
                "critical_range_max": format_range_value(critical_max),
                "reference_range_text": reference_words or None,
                "interpretation_name": interpretation or None,
                "result_key": slug_key(param_name),
            }
        )

    return tests, particulars_by_test


def build_catalog() -> dict:
    prices = load_price_rows()
    meta, particulars = load_parameter_rows()

    catalog_tests: list[dict] = []

    all_ids = sorted(set(prices) | set(meta), key=lambda value: int(value))

    for test_id in all_ids:
        info = meta.get(test_id, {})
        price_info = prices.get(test_id, {})

        test_code = info.get("test_code") or price_info.get("test_code") or ""
        name = info.get("name") or (f"Test {test_code}" if test_code else f"Test {test_id}")
        category = info.get("category", "General Pathology")
        head = info.get("head", normalize_head(category))
        price = price_info.get("price", 0.0)

        catalog_tests.append(
            {
                "id": int(test_id),
                "external_id": int(test_id),
                "name": name,
                "test_code": test_code,
                "price": price,
                "type": "Routine",
                "category": "Pathology",
                "head": head,
                "report": "Same Day",
                "particulars": particulars.get(test_id, []),
            }
        )

    return {
        "generated_from": "lab-tests",
        "test_count": len(catalog_tests),
        "particular_count": sum(len(test["particulars"]) for test in catalog_tests),
        "tests": catalog_tests,
    }


def main() -> None:
    if not LAB_TESTS_DIR.exists():
        raise SystemExit(f"Missing folder: {LAB_TESTS_DIR}")

    catalog = build_catalog()
    OUT.parent.mkdir(parents=True, exist_ok=True)
    OUT.write_text(json.dumps(catalog, indent=2, ensure_ascii=False), encoding="utf-8")

    print(
        f"Wrote {catalog['test_count']} tests "
        f"({catalog['particular_count']} particulars) -> {OUT}"
    )


if __name__ == "__main__":
    main()
