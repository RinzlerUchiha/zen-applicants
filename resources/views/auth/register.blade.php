<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Application</title>

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js"
        integrity="sha384-I7E8VVD/ismYTF4hNIPjVp/Zjvgyol6VFvRkX/vR+Vc4jQkC+hVqc2pM8ODewa9r" crossorigin="anonymous">
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.min.js"
        integrity="sha384-0pUGZvbkm6XF6gxjEnlmuGrJXVbNuzT9qBBavbLwCsOGabYfZo0T0to5eqruptLy" crossorigin="anonymous">
    </script>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css"
        integrity="sha512-Evv84Mr4kqVGRNSgIGL/F/aIDqQb7xQ2vcrdIwxfjThSH8CSR7PBEakCr51Ck+w+/U6swU2Im1vVX0SVk9ABhg=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/js/all.min.js"
        integrity="sha512-b+nQTCdtTBIRIbraqNEwsjB6UvL3UEMkXnhzd8awtCYh0Kcsjl9uEgwVFVbhoj3uu1DO1ZMacNvLoyJJiNfcvg=="
        crossorigin="anonymous" referrerpolicy="no-referrer"></script>


    <style>
        #form-personal input,
        #form-personal select {
            font-size: 12px;
        }

        #form-personal .form-floating>label {
            font-size: 14px;
        }

        #personal-img-preview:hover {
            transform: scale(1.7);
        }

        .form-control-plaintext.border-bottom {
            padding-bottom: 1px !important;
        }
    </style>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('.select-province').forEach(function(selectProvince) {
                selectProvince.addEventListener('change', function() {
                    let row = selectProvince.closest('.row');
                    let selectCity = row.querySelector('.select-city');

                    let optionsCity = selectCity.querySelectorAll('option');
                    optionsCity.forEach(function(option) {
                        if (option.value !== "") {
                            option.style.display = 'none';
                        }
                    });

                    let matchingOptions = selectCity.querySelectorAll('option[province="' +
                        selectProvince.value + '"]');
                    matchingOptions.forEach(function(option) {
                        option.style.display = 'block';
                    });
                });
            });

            document.querySelectorAll('.select-city').forEach(function(selectCity) {
                selectCity.addEventListener('change', function() {
                    let row = selectCity.closest('.row');
                    let selectBarangay = row.querySelector('.select-barangay');

                    let optionsBarangay = selectBarangay.querySelectorAll('option');
                    optionsBarangay.forEach(function(option) {
                        if (option.value !== "") {
                            option.style.display = 'none';
                        }
                    });

                    let matchingOptionsBarangay = selectBarangay.querySelectorAll('option[city="' +
                        selectCity.value + '"]');
                    matchingOptionsBarangay.forEach(function(option) {
                        option.style.display = 'block';
                    });
                });
            });

            document.getElementById('personal-birthdate').addEventListener('change', function() {
                // Get the birthdate from the input field
                const birthdate = this.value;


                // If no birthdate is selected, alert the user
                if (!birthdate) {
                    document.getElementById("personal-age").value = '';
                    return;
                }

                // Convert the input string (YYYY-MM-DD) into a Date object
                const birthDateObj = new Date(birthdate);

                // Get today's date
                const today = new Date();

                // Calculate the age difference in years
                let age = today.getFullYear() - birthDateObj.getFullYear();

                // Adjust the age if the birthday hasn't occurred yet this year
                const monthDifference = today.getMonth() - birthDateObj.getMonth();
                const dayDifference = today.getDate() - birthDateObj.getDate();

                // If the birthdate hasn't occurred yet this year, subtract 1 from the age
                if (monthDifference < 0 || (monthDifference === 0 && dayDifference < 0)) {
                    age--;
                }

                // Display the result
                document.getElementById("personal-age").value = age;
            });

            document.getElementById('btn-set-pass').addEventListener('click', function() {
                if(document.getElementById('input-set-pass').value){
                    document.getElementById('app-code').value = document.getElementById('input-set-pass').value;
                    document.getElementById('form-personal').submit();
                }
            });

            document.getElementById('btn-show-set-pass').addEventListener('click', function() {
                if(document.getElementById('form-personal').reportValidity()){
                    document.getElementById('input-set-pass').value = '';
                    let myModal = new bootstrap.Modal(document.getElementById('setPassModal'));
                    myModal.show();
                }
            });

        });
    </script>
</head>

<body>
    <div class="container-fluid pt-3">
        <div class="row justify-content-md-center">
            <div class="col-md-8">
                <div class="d-flex justify-content-end">
                    <a href="{{ route('login') }}" class="btn btn-outline-primary">Login</a>
                </div>

                <form id="form-personal" class="mb-3" action="{{ route('register.store') }}" method="POST">
                    <!-- Success Message -->
                    @if (session('success'))
                        <div style="color: green;">
                            {{ session('success') }}
                        </div>
                    @endif
                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul>
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                    @csrf
                    <fieldset>
                        <input type="password" name="app-code" id="app-code" style="display: none;">
                        <div class="row g-3">
                            <div class="col">
                                <div class="form-floating mb-3">
                                    <input type="text" class="form-control-plaintext border-bottom"
                                        name="position-applied" id="position-applied" value="{{ old('position-applied') }}" required>
                                    <label for="position-applied">Position Applied</label>
                                </div>
                            </div>
                        </div>
                        <div class="row g-3">
                            <div class="col-md-auto">
                                <div class="form-floating mb-3">
                                    <input type="text" class="form-control-plaintext border-bottom"
                                        name="personal-firstname" id="personal-firstname" value="{{ old('personal-firstname') }}">
                                    <label for="personal-firstname">First Name</label>
                                </div>
                            </div>
                            <div class="col-md-auto">
                                <div class="form-floating mb-3">
                                    <input type="text" class="form-control-plaintext border-bottom"
                                        name="personal-middlename" id="personal-middlename" value="{{ old('position-middlename') }}">
                                    <label for="personal-middlename">Middle Name</label>
                                </div>
                            </div>
                            <div class="col-md-auto">
                                <div class="form-floating mb-3">
                                    <input type="text" class="form-control-plaintext border-bottom"
                                        name="personal-lastname" id="personal-lastname" value="{{ old('position-lastname') }}">
                                    <label for="personal-lastname">Last Name</label>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-floating mb-3">
                                    <input type="text" class="form-control-plaintext border-bottom"
                                        name="personal-suffix" id="personal-suffix" value="{{ old('position-suffix') }}">
                                    <label for="personal-suffix">Suffix</label>
                                </div>
                            </div>
                        </div>

                        <!-- contact -->
                        <h6 class="mt-3">Contact Info</h6>
                        <div class="row g-3">
                            <div class="col-md-4">
                                <div class="form-floating mb-3">
                                    <input type="email" class="form-control-plaintext border-bottom"
                                        name="personal-email" id="personal-email" value="{{ old('position-email') }}">
                                    <label for="personal-email">Email</label>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-floating mb-3">
                                    <input type="text" class="form-control-plaintext border-bottom"
                                        name="personal-contact" id="personal-contact" value="{{ old('position-contact') }}">
                                    <label for="personal-contact">Personal Contact</label>
                                </div>
                            </div>
                            <div class="col-md">
                                <div class="form-floating mb-3">
                                    <input type="text" class="form-control-plaintext border-bottom"
                                        name="personal-telephone" id="personal-telephone" value="{{ old('position-telephone') }}">
                                    <label for="personal-telephone">Telephone</label>
                                </div>
                            </div>
                        </div>

                        <!-- address -->
                        <h6 class="mt-3">Permanent Address</h6>
                        <div class="row g-3">
                            <div class="col-md-3">
                                <div class="form-floating mb-3">
                                    <select class="form-control-plaintext border-bottom select-province"
                                        name="personal-padd-province" id="personal-padd-province" aria-label="">
                                        <option value>-Select-</option>
                                        @foreach ($provinceList as $list)
                                            <option value="{{ $list->pr_name }}" {{ old('personal-padd-province' == $list->pr_name ? 'selected' : '') }}>{{ $list->pr_name }}</option>
                                        @endforeach
                                    </select>
                                    <label for="personal-padd-province">Province</label>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-floating mb-3">
                                    <select class="form-control-plaintext border-bottom select-city"
                                        name="personal-padd-city" id="personal-padd-city" aria-label="">
                                        <option value>-Select-</option>
                                        @foreach ($municipalityList as $list)
                                            <option style="display: none;" province="{{ $list->ct_province_name }}"
                                                value="{{ $list->ct_name }}" {{ old('personal-padd-city' == $list->ct_name ? 'selected' : '') }}>{{ $list->ct_name }}</option>
                                        @endforeach
                                    </select>
                                    <label for="personal-padd-city">City</label>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-floating mb-3">
                                    <select class="form-control-plaintext border-bottom select-barangay"
                                        name="personal-padd-barangay" id="personal-padd-barangay" aria-label="">
                                        <option value>-Select-</option>
                                        @foreach ($barangayList as $list)
                                            <option style="display: none;" city="{{ $list->br_city_name }}"
                                                value="{{ $list->br_name }}" {{ old('personal-padd-barangay' == $list->br_name ? 'selected' : '') }}>{{ $list->br_name }}</option>
                                        @endforeach
                                    </select>
                                    <label for="personal-padd-barangay">Barangay</label>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-floating mb-3">
                                    <input type="text" class="form-control-plaintext border-bottom"
                                        name="personal-padd-specific" id="personal-padd-specific" value="{{ old('position-padd-specific') }}">
                                    <label for="personal-padd-specific">Street/House #</label>
                                </div>
                            </div>
                        </div>

                        <h6 class="mt-3">Current Address</h6>
                        <div class="row g-3">
                            <div class="col-md-3">
                                <div class="form-floating mb-3">
                                    <select class="form-control-plaintext border-bottom select-province"
                                        name="personal-cadd-province" id="personal-cadd-province" aria-label="">
                                        <option value>-Select-</option>
                                        @foreach ($provinceList as $list)
                                            <option value="{{ $list->pr_name }}" {{ old('personal-cadd-province' == $list->pr_name ? 'selected' : '') }}>{{ $list->pr_name }}</option>
                                        @endforeach
                                    </select>
                                    <label for="personal-cadd-province">Province</label>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-floating mb-3">
                                    <select class="form-control-plaintext border-bottom select-city"
                                        name="personal-cadd-city" id="personal-cadd-city" aria-label="">
                                        <option value>-Select-</option>
                                        @foreach ($municipalityList as $list)
                                            <option style="display: none;" province="{{ $list->ct_province_name }}"
                                                value="{{ $list->ct_name }}" {{ old('personal-cadd-city' == $list->ct_name ? 'selected' : '') }}>{{ $list->ct_name }}</option>
                                        @endforeach
                                    </select>
                                    <label for="personal-cadd-city">City</label>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-floating mb-3">
                                    <select class="form-control-plaintext border-bottom select-barangay"
                                        name="personal-cadd-barangay" id="personal-cadd-barangay" aria-label="">
                                        <option value>-Select-</option>
                                        @foreach ($barangayList as $list)
                                            <option style="display: none;" city="{{ $list->br_city_name }}"
                                                value="{{ $list->br_name }}" {{ old('personal-cadd-barangay' == $list->br_name ? 'selected' : '') }}>{{ $list->br_name }}</option>
                                        @endforeach
                                    </select>
                                    <label for="personal-cadd-barangay">Barangay</label>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-floating mb-3">
                                    <input type="text" class="form-control-plaintext border-bottom"
                                        name="personal-cadd-specific" id="personal-cadd-specific" value="{{ old('position-cadd-specific') }}">
                                    <label for="personal-cadd-specific">Street/House #</label>
                                </div>
                            </div>
                        </div>

                        <h6 class="mt-3">Place Of Birth</h6>
                        <div class="row g-3">
                            <div class="col-md-3">
                                <div class="form-floating mb-3">
                                    <select class="form-control-plaintext border-bottom select-province"
                                        name="personal-badd-province" id="personal-badd-province" aria-label="">
                                        <option value>-Select-</option>
                                        @foreach ($provinceList as $list)
                                            <option value="{{ $list->pr_name }}" {{ old('personal-badd-province' == $list->pr_name ? 'selected' : '') }}>{{ $list->pr_name }}</option>
                                        @endforeach
                                    </select>
                                    <label for="personal-badd-province">Province</label>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-floating mb-3">
                                    <select class="form-control-plaintext border-bottom select-city"
                                        name="personal-badd-city" id="personal-badd-city" aria-label="">
                                        <option value>-Select-</option>
                                        @foreach ($municipalityList as $list)
                                            <option style="display: none;" province="{{ $list->ct_province_name }}"
                                                value="{{ $list->ct_name }}" {{ old('personal-badd-city' == $list->ct_name ? 'selected' : '') }}>{{ $list->ct_name }}</option>
                                        @endforeach
                                    </select>
                                    <label for="personal-badd-city">City</label>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-floating mb-3">
                                    <select class="form-control-plaintext border-bottom select-barangay"
                                        name="personal-badd-barangay" id="personal-badd-barangay" aria-label="">
                                        <option value>-Select-</option>
                                        @foreach ($barangayList as $list)
                                            <option style="display: none;" city="{{ $list->br_city_name }}"
                                                value="{{ $list->br_name }}" {{ old('personal-badd-barangay' == $list->br_name ? 'selected' : '') }}>{{ $list->br_name }}</option>
                                        @endforeach
                                    </select>
                                    <label for="personal-badd-barangay">Barangay</label>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-floating mb-3">
                                    <input type="text" class="form-control-plaintext border-bottom"
                                        name="personal-badd-specific" id="personal-badd-specific" value="{{ old('position-badd-specific') }}">
                                    <label for="personal-badd-specific">Street/House #</label>
                                </div>
                            </div>
                        </div>

                        <!-- Basic Info -->
                        <h6 class="mt-3">Basic Info</h6>
                        <div class="row g-3">
                            <div class="col-md-auto">
                                <div class="form-floating mb-3">
                                    <input type="date" class="form-control-plaintext border-bottom"
                                        name="personal-birthdate" id="personal-birthdate" value="{{ old('position-birthdate') }}">
                                    <label for="personal-birthdate">Birth Date</label>
                                </div>
                            </div>
                            <div class="col-md-1">
                                <div class="form-floating mb-3">
                                    <input type="text" readonly class="form-control-plaintext border-bottom"
                                        id="personal-age" value="{{ old('position-age') }}">
                                    <label for="personal-age">Age</label>
                                </div>
                            </div>
                            <div class="col-md-auto">
                                <div class="form-floating mb-3">
                                    <select class="form-control-plaintext border-bottom" name="personal-civil-status"
                                        id="personal-civil-status" aria-label="">
                                        <option value>-Select-</option>
                                        <option value="Single" {{ old('personal-civil-status' == 'Single' ? 'selected' : '') }}>Single</option>
                                        <option value="Married" {{ old('personal-civil-status' == 'Married' ? 'selected' : '') }}>Married</option>
                                        <option value="Separated/Divorced" {{ old('personal-civil-status' == 'Separated/Divorced' ? 'selected' : '') }}>Separated/Divorced</option>
                                        <option value="Widow/Widower" {{ old('personal-civil-status' == 'Widow/Widower' ? 'selected' : '') }}>Widow/Widower</option>
                                    </select>
                                    <label for="personal-civil-status">Civil Status</label>
                                </div>
                            </div>
                            <div class="col-md-auto">
                                <div class="form-floating mb-3">
                                    <select class="form-control-plaintext border-bottom" name="personal-sex"
                                        id="personal-sex" aria-label="">
                                        <option value>-Select-</option>
                                        <option value="Male" {{ old('personal-sex' == 'Male' ? 'selected' : '') }}>Male</option>
                                        <option value="Female" {{ old('personal-sex' == 'Female' ? 'selected' : '') }}>Female</option>
                                    </select>
                                    <label for="personal-sex">Sex</label>
                                </div>
                            </div>
                            <div class="col-md-auto">
                                <div class="form-floating mb-3">
                                    <select class="form-control-plaintext border-bottom" name="personal-bloodtype"
                                        id="personal-bloodtype" aria-label="">
                                        <option value>-Select-</option>
                                        <option value="O+" {{ old('personal-bloodtype' == 'O+' ? 'selected' : '') }}>O+</option>
                                        <option value="O-" {{ old('personal-bloodtype' == 'O-' ? 'selected' : '') }}>O-</option>
                                        <option value="A+" {{ old('personal-bloodtype' == 'A+' ? 'selected' : '') }}>A+</option>
                                        <option value="A-" {{ old('personal-bloodtype' == 'A-' ? 'selected' : '') }}>A-</option>
                                        <option value="B+" {{ old('personal-bloodtype' == 'B+' ? 'selected' : '') }}>B+</option>
                                        <option value="B-" {{ old('personal-bloodtype' == 'B-' ? 'selected' : '') }}>B-</option>
                                        <option value="AB+" {{ old('personal-bloodtype' == 'AB+' ? 'selected' : '') }}>AB+</option>
                                        <option value="AB-" {{ old('personal-bloodtype' == 'AB-' ? 'selected' : '') }}>AB-</option>
                                    </select>
                                    <label for="personal-bloodtype">Blood Type</label>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-floating mb-3">
                                    <input type="text" class="form-control-plaintext border-bottom"
                                        name="personal-height" id="personal-height" value="{{ old('position-height') }}">
                                    <label for="personal-height">Height (cm)</label>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-floating mb-3">
                                    <input type="text" class="form-control-plaintext border-bottom"
                                        name="personal-weight" id="personal-weight" value="{{ old('position-weight') }}">
                                    <label for="personal-weight">Weight (kg)</label>
                                </div>
                            </div>

                            <div class="col-md-auto">
                                <div class="form-floating mb-3">
                                    <input type="text" class="form-control-plaintext border-bottom"
                                        name="personal-nationality" id="personal-nationality" value="{{ old('position-nationality') }}">
                                    <label for="personal-nationality">Dialect</label>
                                </div>
                            </div>

                            <div class="col-md-auto">
                                <div class="form-floating mb-3">
                                    <input type="text" class="form-control-plaintext border-bottom"
                                        name="personal-religion" id="personal-religion" value="{{ old('position-religion') }}">
                                    <label for="personal-religion">Religion</label>
                                </div>
                            </div>
                            <div class="col-md-auto">
                                <div class="form-floating mb-3">
                                    <input type="text" class="form-control-plaintext border-bottom"
                                        name="personal-dialect" id="personal-dialect" value="{{ old('position-dialect') }}">
                                    <label for="personal-dialect">Dialect</label>
                                </div>
                            </div>
                        </div>

                        <!-- gov accounts -->
                        <h6 class="mt-3">Government Identification Numbers</h6>
                        <div class="row g-3">
                            <div class="col-md-3">
                                <div class="form-floating mb-3">
                                    <input type="text" class="form-control-plaintext border-bottom"
                                        name="personal-sss" id="personal-sss" value="{{ old('position-sss') }}">
                                    <label for="personal-sss">SSS #</label>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-floating mb-3">
                                    <input type="text" class="form-control-plaintext border-bottom"
                                        name="personal-hdmf" id="personal-hdmf" value="{{ old('position-hdmf') }}">
                                    <label for="personal-hdmf">Pagibig #</label>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-floating mb-3">
                                    <input type="text" class="form-control-plaintext border-bottom"
                                        name="personal-phic" id="personal-phic" value="{{ old('position-phic') }}">
                                    <label for="personal-phic">Philhealth #</label>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-floating mb-3">
                                    <input type="text" class="form-control-plaintext border-bottom"
                                        name="personal-tin" id="personal-tin" value="{{ old('position-tin') }}">
                                    <label for="personal-tin">TIN #</label>
                                </div>
                            </div>
                        </div>
                    </fieldset>
                    <div class="d-flex justify-content-center mt-3 mb-5">
                        <button type="button" class="btn btn-primary mx-1 px-5 flex-md-grow-0 flex-grow-1"
                            id="btn-show-set-pass">Submit</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal -->
    <div class="modal fade" id="setPassModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
        aria-labelledby="setPassModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title fs-5" id="setPassModalLabel">Set Password</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="password" class="form-control" id="input-set-pass">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary" id="btn-set-pass">Proceed</button>
                </div>
            </div>
        </div>
    </div>
</body>

</html>
