@extends('layouts.app')

@section('content')
    <div class="flex items-center justify-between mb-6">
        <h2 class="text-3xl font-bold text-gray-800">User Manager</h2>
        <a href="" class="bg-gray-200 hover:bg-gray-300 text-gray-800 font-medium py-2 px-4 rounded-lg shadow-md transition-colors duration-200 ease-in-out flex items-center">
            <svg class="w-5 h-5 inline-block mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            Back to Dashboard
        </a>
    </div>

    <!-- User Management Form -->
    <div class="bg-white rounded-xl shadow-lg p-6 mb-8">
        <form action="#" method="POST"> {{-- Action will be updated later for actual submission --}}
            @csrf {{-- Laravel CSRF token --}}

            <!-- User Selection and Details -->
            <h3 class="text-2xl font-semibold text-gray-800 mb-4 border-b pb-2">User Details</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-6">
                <div>
                    <label for="user_id" class="block text-gray-700 text-sm font-bold mb-2">Select User:</label>
                    <select id="user_id" name="user_id" class="shadow appearance-none border rounded-lg w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" required>
                        <option value="">-- Select User --</option>
                        {{-- Static Users (example) --}}
                        <option value="U001" data-branch="Main" data-fullname="Admin User" data-username="admin">U001 - Admin User</option>
                        <option value="U002" data-branch="OPD" data-fullname="Receptionist A" data-username="receptionist.a">U002 - Receptionist A</option>
                        <option value="U003" data-branch="Pharmacy" data-fullname="Pharmacist B" data-username="pharmacist.b">U003 - Pharmacist B</option>
                        <option value="U004" data-branch="Lab" data-fullname="Lab Technician C" data-username="lab.c">U004 - Lab Technician C</option>
                    </select>
                </div>
                <div>
                    <label for="branch" class="block text-gray-700 text-sm font-bold mb-2">Branch:</label>
                    <input type="text" id="branch" name="branch" class="shadow appearance-none border rounded-lg w-full py-2 px-3 text-gray-700 bg-gray-100 leading-tight focus:outline-none" readonly placeholder="Auto-populated">
                </div>
                <div>
                    <label for="full_name" class="block text-gray-700 text-sm font-bold mb-2">Full Name:</label>
                    <input type="text" id="full_name" name="full_name" class="shadow appearance-none border rounded-lg w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" placeholder="Full Name" required>
                </div>
                <div>
                    <label for="username" class="block text-gray-700 text-sm font-bold mb-2">Username:</label>
                    <input type="text" id="username" name="username" class="shadow appearance-none border rounded-lg w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" placeholder="Username" required>
                </div>
                <div>
                    <label for="password" class="block text-gray-700 text-sm font-bold mb-2">Password:</label>
                    <input type="password" id="password" name="password" class="shadow appearance-none border rounded-lg w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" placeholder="Set New Password (optional)">
                    <p class="text-xs text-gray-500 mt-1">Leave blank to keep current password.</p>
                </div>
            </div>

            <!-- Functionalities Assignment -->
            <h3 class="text-2xl font-semibold text-gray-800 mb-4 border-b pb-2 mt-8">Assign Functionalities</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 mb-6">
                {{-- Static List of Functionalities (example) --}}
                @php
                    $functionalities = [
                        'Dashboard Access', 'Departments: Add Department', 'Departments: Add Speciality',
                        'Departments: Add Floors', 'Departments: Add Rooms', 'Departments: Add Doctor Type',
                        'Departments: Add Shifts', 'Departments: Emergency Charges',
                        'Patients: Indoor Registration', 'Patients: Outdoor Registration', 'Patients: View All Patients',
                        'Patients: Admission Form', 'Patients: Discharge Form', 'Patients: Birth Certificates', 'Patients: Death Certificates',
                        'Doctors: Add Doctor', 'Doctors: View All Doctors', 'OPD: Consultation',
                        'Billing: Generate Invoice', 'Reports: View Financial Reports', 'Admin: User Management'
                    ];
                @endphp
                @foreach($functionalities as $func)
                    <label class="inline-flex items-center">
                        <input type="checkbox" name="functionalities[]" value="{{ $func }}" class="form-checkbox h-5 w-5 text-blue-600 rounded focus:ring-blue-500">
                        <span class="ml-2 text-gray-700 text-sm">{{ $func }}</span>
                    </label>
                @endforeach
            </div>

            <!-- Group Management Section -->
            <h3 class="text-2xl font-semibold text-gray-800 mb-4 border-b pb-2 mt-8">Manage Groups</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <div>
                    <label for="assign_group" class="block text-gray-700 text-sm font-bold mb-2">Assign Group to User:</label>
                    <select id="assign_group" name="assign_group" class="shadow appearance-none border rounded-lg w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        <option value="">-- Select Group --</option>
                        {{-- Static Groups (example) --}}
                        <option value="Admin">Admin</option>
                        <option value="Receptionist">Receptionist</option>
                        <option value="Doctor">Doctor</option>
                        <option value="Pharmacist">Pharmacist</option>
                    </select>
                    <button type="button" class="mt-2 bg-green-500 hover:bg-green-600 text-white font-bold py-2 px-4 rounded-full shadow-md transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2">Assign Group</button>
                </div>
                <div>
                    <label for="create_new_group" class="block text-gray-700 text-sm font-bold mb-2">Create New Group:</label>
                    <input type="text" id="create_new_group" name="create_new_group" class="shadow appearance-none border rounded-lg w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" placeholder="New Group Name">
                    <button type="button" class="mt-2 bg-blue-500 hover:bg-blue-600 text-white font-bold py-2 px-4 rounded-full shadow-md transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">Create Group</button>
                </div>
            </div>
            <div class="mb-6">
                <h4 class="text-lg font-semibold text-gray-800 mb-2">Current User's Assigned Groups:</h4>
                <div id="assigned_groups" class="flex flex-wrap gap-2">
                    {{-- Dynamically populated based on selected user in a real app --}}
                    <span class="bg-gray-200 text-gray-800 px-3 py-1 rounded-full text-sm flex items-center">
                        Admin
                        <button type="button" class="ml-2 text-red-500 hover:text-red-700 text-xs font-bold">x</button>
                    </span>
                    <span class="bg-gray-200 text-gray-800 px-3 py-1 rounded-full text-sm flex items-center">
                        IT Support
                        <button type="button" class="ml-2 text-red-500 hover:text-red-700 text-xs font-bold">x</button>
                    </span>
                </div>
            </div>


            <div class="flex justify-end mt-6">
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-6 rounded-full shadow-lg transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                    Save User Permissions
                </button>
            </div>
        </form>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const userIdSelect = document.getElementById('user_id');
            const branchInput = document.getElementById('branch');
            const fullNameInput = document.getElementById('full_name');
            const usernameInput = document.getElementById('username');
            const functionalitiesCheckboxes = document.querySelectorAll('input[name="functionalities[]"]');
            const assignedGroupsDiv = document.getElementById('assigned_groups');

            // Static user data for demonstration
            const staticUsers = {
                'U001': {
                    branch: 'Main',
                    fullName: 'Admin User',
                    username: 'admin',
                    assignedFunc: ['Dashboard Access', 'Departments: Add Department', 'Admin: User Management'],
                    assignedGroups: ['Admin', 'IT Support']
                },
                'U002': {
                    branch: 'OPD',
                    fullName: 'Receptionist A',
                    username: 'receptionist.a',
                    assignedFunc: ['Patients: Outdoor Registration', 'OPD: Consultation'],
                    assignedGroups: ['Receptionist']
                },
                'U003': {
                    branch: 'Pharmacy',
                    fullName: 'Pharmacist B',
                    username: 'pharmacist.b',
                    assignedFunc: ['Billing: Generate Invoice'],
                    assignedGroups: ['Pharmacist']
                },
                'U004': {
                    branch: 'Lab',
                    fullName: 'Lab Technician C',
                    username: 'lab.c',
                    assignedFunc: ['Reports: View Financial Reports'],
                    assignedGroups: []
                }
            };

            function loadUserDetails(userId) {
                const user = staticUsers[userId];

                // Clear previous selections
                functionalitiesCheckboxes.forEach(cb => cb.checked = false);
                assignedGroupsDiv.innerHTML = '';

                if (user) {
                    branchInput.value = user.branch;
                    fullNameInput.value = user.fullName;
                    usernameInput.value = user.username;

                    // Check assigned functionalities
                    user.assignedFunc.forEach(func => {
                        const checkbox = document.querySelector(`input[name="functionalities[]"][value="${func}"]`);
                        if (checkbox) {
                            checkbox.checked = true;
                        }
                    });

                    // Display assigned groups
                    user.assignedGroups.forEach(group => {
                        const span = document.createElement('span');
                        span.className = 'bg-gray-200 text-gray-800 px-3 py-1 rounded-full text-sm flex items-center';
                        span.innerHTML = `${group} <button type="button" class="ml-2 text-red-500 hover:text-red-700 text-xs font-bold remove-group-btn" data-group="${group}">x</button>`;
                        assignedGroupsDiv.appendChild(span);
                    });
                } else {
                    branchInput.value = '';
                    fullNameInput.value = '';
                    usernameInput.value = '';
                }
            }

            // Event listener for user selection
            userIdSelect.addEventListener('change', function() {
                loadUserDetails(this.value);
            });

            // Handle remove group button (for static example)
            assignedGroupsDiv.addEventListener('click', function(event) {
                if (event.target.classList.contains('remove-group-btn')) {
                    const groupToRemove = event.target.dataset.group;
                    alert(`Simulating removal of group: ${groupToRemove}`);
                    // In a real app, you'd send an AJAX request to remove the group from the user
                    event.target.closest('span').remove(); // Remove from UI
                }
            });

            // Initial load if a user is pre-selected (e.g., after form submission with errors)
            if (userIdSelect.value) {
                loadUserDetails(userIdSelect.value);
            }
        });
    </script>
@endsection
