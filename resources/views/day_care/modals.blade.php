<div id="patient_search_modal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden">
    <div class="relative top-20 mx-auto p-5 border w-full md:w-3/4 lg:w-1/2 shadow-lg rounded-md bg-white">
        <h3 class="text-xl font-semibold mb-4">Search Patient</h3>
        <input type="text" id="patient_search_query" class="w-full px-3 py-2 border rounded-md" placeholder="Enter Name, CNIC, or Phone">
        <div id="patient_search_results" class="mt-4 max-h-60 overflow-y-auto"></div>
        <div class="text-right mt-4">
            <button id="close_patient_modal" class="px-4 py-2 bg-gray-300 rounded-md">Close</button>
        </div>
    </div>
</div>

<div id="consultant_search_modal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden">
    <div class="relative top-20 mx-auto p-5 border w-full md:w-3/4 lg:w-1/2 shadow-lg rounded-md bg-white">
        <h3 class="text-xl font-semibold mb-4">Search Consultant</h3>
        <input type="text" id="consultant_search_query" class="w-full px-3 py-2 border rounded-md" placeholder="Enter Name, Department, or Doc No">
        <div id="consultant_search_results" class="mt-4 max-h-60 overflow-y-auto"></div>
        <div class="text-right mt-4">
            <button id="close_consultant_modal" class="px-4 py-2 bg-gray-300 rounded-md">Close</button>
        </div>
    </div>
</div>