<?php
// Initialize variables if not already set
$firstName = $firstName ?? '';
$middleName = $middleName ?? '';
$lastName = $lastName ?? '';
$email = $email ?? '';
$contactNumber = $contactNumber ?? '';
$dateOfBirth = $dateOfBirth ?? '';
$country = $country ?? '';
$city = $city ?? '';
$streetname = $streetname ?? '';
$barangay = $barangay ?? '';
$province = $province ?? '';
?>


<!-- Button to open the Update Profile modal -->
<!-- Update Modal -->
<!-- Update Modal -->
<div class="modal fade" id="updateModal" tabindex="-1" aria-labelledby="updateModalLabel" aria-hidden="true">
    <div class="modal-dialog" style="max-width: 50%;">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="updateModalLabel">Update Profile</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" style="overflow-y: auto; max-height: 80vh;">
                <form action="../helpers/update_profile.php" method="post" enctype="multipart/form-data">
                    <!-- First Name & Last Name in a row -->
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label for="firstName" class="form-label">First Name</label>
                            <input type="text" class="form-control" id="firstName" name="firstName" value="<?php echo htmlspecialchars($firstName); ?>" required>
                        </div>
                        <div class="col-md-4">
                            <label for="middleName" class="form-label">Middle Name</label>
                            <input type="text" class="form-control" id="middleName" name="middleName" value="<?php echo htmlspecialchars($middleName); ?>" placeholder="Enter your middle name">
                        </div>
                        <div class="col-md-4">
                            <label for="lastName" class="form-label">Last Name</label>
                            <input type="text" class="form-control" id="lastName" name="lastName" value="<?php echo htmlspecialchars($lastName); ?>" required>
                        </div>
                    </div>

                    <!-- Email & Contact Number in a row -->
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($email); ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label for="contactNumber" class="form-label">Contact Number</label>
                            <input type="text" class="form-control" id="contactNumber" name="contactNumber" value="<?php echo htmlspecialchars($contactNumber); ?>" required>
                        </div>
                    </div>

                    <!-- Password & Age in a row -->
                    <div class="row mb-3">
                        <div class="col-md-12">
                            <label for="password" class="form-label">New Password</label>
                            <input type="password" class="form-control" id="password" name="password" placeholder="Enter new password">
                        </div>
                        <div class="mb-6">
                            <label for="date_of_birth" class="form-label">Date of Birth</label>
                            <input type="date" class="form-control" id="date_of_birth" name="date_of_birth" value="<?php echo htmlspecialchars($user['date_of_birth']); ?>" required>
                        </div>
                    </div>

                    <!-- Country & City in a row -->
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="country" class="form-label">Country</label>
                            <input type="text" class="form-control" id="country" name="country" value="<?php echo htmlspecialchars($country); ?>">
                        </div>
                        <div class="col-md-6">
                            <label for="city" class="form-label">City</label>
                            <input type="text" class="form-control" id="city" name="city" value="<?php echo htmlspecialchars($city); ?>">
                        </div>
                    </div>

                    <!-- Street, Barangay, Province in a row -->
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label for="streetname" class="form-label">Street Name</label>
                            <input type="text" class="form-control" id="streetname" name="streetname" value="<?php echo htmlspecialchars($streetname); ?>">
                        </div>
                        <div class="col-md-4">
                            <label for="barangay" class="form-label">Barangay</label>
                            <input type="text" class="form-control" id="barangay" name="barangay" value="<?php echo htmlspecialchars($barangay); ?>">
                        </div>
                        <div class="col-md-4">
                            <label for="province" class="form-label">Province</label>
                            <input type="text" class="form-control" id="province" name="province" value="<?php echo htmlspecialchars($province); ?>">
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary">Save Changes</button>
                </form>
            </div>
        </div>
    </div>
</div>



<!-- Update Photo Modal -->
<div class="modal fade" id="updatePhotoModal" tabindex="-1" aria-labelledby="updatePhotoModalLabel"
    aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="updatePhotoModalLabel">Update Profile Photo</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form action="../helpers/update_photo.php" method="post" enctype="multipart/form-data">
                    <div class="mb-3">
                        <label for="profile_pics" class="form-label">Choose Photo</label>
                        <input type="file" class="form-control" id="profile_pics" name="photo" accept="image/*" required>
                    </div>
                    <div class="mb-3">
                        <img id="preview" src="#" alt="Preview" style="max-width: 100%; max-height: 200px;">
                    </div>
                    <button type="submit" class="btn btn-primary">Upload Photo</button>
                </form>
            </div>
        </div>
    </div>
</div>


<!-- Registration Modal -->
<!-- Registration Modal -->
<!-- Registration Modal -->
<div class="modal fade" id="registrationModal" tabindex="-1" aria-labelledby="registrationModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="registrationModalLabel">Sign Up</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body row">
                <form action="auth/register.php" method="post">
                    <div class="col-md-18">
                        <div class="mb-3">
                            <label for="firstName" class="form-label">First Name</label>
                            <input type="text" class="form-control" id="firstName" name="firstName" placeholder="Enter your first name" required>
                        </div>
                        <div class="mb-3">
                            <label for="middleName" class="form-label">Middle Name</label>
                            <input type="text" class="form-control" id="middleName" name="middleName" placeholder="Enter your middle name">
                        </div>
                        <div class="mb-3">
                            <label for="lastName" class="form-label">Last Name</label>
                            <input type="text" class="form-control" id="lastName" name="lastName" placeholder="Enter your last name" required>
                        </div>
                        <div class="mb-3">
                            <label for="contactNumber" class="form-label">Contact Number</label>
                            <input type="text" class="form-control" id="contactNumber" name="contactNumber" placeholder="Enter your contact number" required pattern="\d+" title="Please enter only numbers">
                        </div>
                        <div class="mb-3">
                            <label for="date_of_birth" class="form-label">Date of Birth</label>
                            <input type="date" class="form-control" id="date_of_birth" name="date_of_birth" required>
                        </div>
                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="email" name="email" placeholder="Enter your email" required>
                        </div>
                        <div class="mb-3">
                            <label for="password" class="form-label">Password</label>
                            <input type="password" class="form-control" id="password" name="password"
                                placeholder="Enter your password" required
                                pattern="(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{6,}"
                                title="Must contain at least one number, one uppercase, one lowercase letter, and be at least 6 characters long">
                        </div>

                    </div>
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary">Register</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>




<div class="modal fade" id="loginModal" tabindex="-1" aria-labelledby="loginModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="loginModalLabel">Log In</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form action="auth/login.php" method="post">
                    <div class="mb-3">
                        <label for="loginEmail" class="form-label">Email</label>
                        <input type="email" class="form-control" id="loginEmail" name="loginEmail"
                            placeholder="Enter your email" required>
                    </div>
                    <div class="mb-3">
                        <label for="loginPassword" class="form-label">Password</label>
                        <input type="password" class="form-control" id="loginPassword" name="loginPassword"
                            placeholder="Enter your password" required>
                    </div>
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary">Log In</button>
                    </div>
                    <!-- Forgot Password and Sign Up links -->
                    <div class="mt-3 text-center">
                        <a href="#" class="text-decoration-none" data-bs-dismiss="modal" data-bs-toggle="modal" data-bs-target="#forgotPasswordModal">Forgot Password?</a>
                    </div>
                    <div class="mt-3 text-center">
                        <p>Don't have an account? <a href="#" data-bs-dismiss="modal" data-bs-toggle="modal" data-bs-target="#registrationModal" class="text-decoration-none">Sign Up</a></p>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>


<!-- Apply Seller Modal -->
<div class="modal fade" id="applySellerModal" tabindex="-1" aria-labelledby="applySellerModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="applySellerModalLabel">Apply as a Seller</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form action="../helpers/apply_seller.php" method="post" enctype="multipart/form-data">
                    <div class="mb-3">
                        <label for="businessName" class="form-label">Business Name</label>
                        <input type="text" class="form-control" id="businessName" name="businessName" required>
                    </div>
                    <div class="mb-3">
                        <label for="businessDescription" class="form-label">Business Description</label>
                        <textarea class="form-control" id="businessDescription" name="businessDescription" rows="3" required></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="businessAddress" class="form-label">Business Address</label>
                        <input type="text" class="form-control" id="businessAddress" name="businessAddress" required>
                    </div>
                    <div class="mb-3">
                        <label for="businessPermit" class="form-label">Business Permit</label>
                        <input type="file" class="form-control" id="businessPermit" name="businessPermit" accept="image/*, .pdf" required>
                    </div>
                    <div class="mb-3">
                        <label for="healthPermit" class="form-label">Health Permit</label>
                        <input type="file" class="form-control" id="healthPermit" name="healthPermit" accept="image/*, .pdf" required>
                    </div>
                    <button type="submit" class="btn btn-primary">Submit Application</button>
                </form>
            </div>
        </div>
    </div>
</div>



<div class="modal fade" id="applySupplierModal" tabindex="-1" aria-labelledby="applySupplierModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="applySupplierModalLabel">Apply as a Supplier</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form action="../helpers/apply_supplier.php" method="post" enctype="multipart/form-data">
                    <div class="mb-3">
                        <label for="businessName" class="form-label">Business Name</label>
                        <input type="text" class="form-control" id="businessName" name="businessName" required>
                    </div>
                    <div class="mb-3">
                        <label for="businessDescription" class="form-label">Business Description</label>
                        <textarea class="form-control" id="businessDescription" name="businessDescription" rows="3" required></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="businessAddress" class="form-label">Business Address</label>
                        <input type="text" class="form-control" id="businessAddress" name="businessAddress" required>
                    </div>
                    <div class="mb-3">
                        <label for="businessPermit" class="form-label">Business Permit</label>
                        <input type="file" class="form-control" id="businessPermit" name="businessPermit" accept="image/*, .pdf" required>
                    </div>
                    <div class="mb-3">
                        <label for="healthPermit" class="form-label">Health Permit</label>
                        <input type="file" class="form-control" id="healthPermit" name="healthPermit" accept="image/*, .pdf" required>
                    </div>
                    <button type="submit" class="btn btn-primary">Submit Application</button>
                </form>
            </div>
        </div>
    </div>
</div>

