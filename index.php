<?php
// Check if the password is correct
$showForm = false;
$errorMessage = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Check if the password entered is correct
    if ($_POST['password'] === 'password123') { //change password 
        $showForm = true; // Show the form if the password is correct
    } else {
        $errorMessage = 'Incorrect Password'; // Error message if the password is incorrect
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Link</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            background: url('/img.jpeg') no-repeat center center/cover;
            color: #fff;
            position: relative;
        }       

        /* Add a blur effect using an overlay */
        body::before {
            content: "";
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: inherit;
            filter: blur(5px);
            z-index: -1;
        }       

        .form-container {
            background: #1c1c1c;
            padding: 2rem;
            border-radius: 15px;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.3);
            width: 100%;
            max-width: 450px;
            overflow: hidden;
            position: relative;
        }

        .form-container h1 {
            text-align: center;
            margin-bottom: 1rem;
            font-size: 2rem;
            color: #fff;
        }

        .form-group {
            margin-bottom: 1.5rem;
            position: relative;
        }

        .form-group label {
            position: absolute;
            top: 50%;
            left: 15px;
            transform: translateY(-50%);
            transition: 0.3s ease;
            color: rgba(255, 255, 255, 0.7);
            pointer-events: none;
        }

        .form-group input, .form-group textarea{
            width: 95%;
            padding: 12px 12px;
            background: rgba(255, 255, 255, 0.2);
            border: 1px solid rgb(255, 89, 0);
            border-radius: 8px;
            outline: none;
            color: #fff;
            font-size: 1rem;
            transition: 0.3s ease;
        }
        .form-group select {
            width: 100%;
            padding: 12px 12px;
            background: rgba(255, 255, 255, 0.2);
            border: 1px solid rgb(255, 89, 0);
            border-radius: 8px;
            outline: none;
            color: #fff;
            font-size: 1rem;
            transition: 0.3s ease;
        }
        .form-group input:focus,
        .form-group select:focus {
            background: rgba(29, 29, 29, 0.46);
        }

        .form-group input:focus + label,
        .form-group input:not(:placeholder-shown) + label,
        .form-group select:focus + label {
            top: -10px;
            font-size: 0.9rem;
            color: rgb(255, 89, 0);
        }

        .form-container button {
            width: 100%;
            padding: 12px;
            background:rgb(255, 89, 0);
            color: #1f4037;
            border: none;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: bold;
            cursor: pointer;
            transition: 0.3s ease;
        }

        .form-container button:hover {
            background:rgb(255, 8, 0);
            color: #fff;
        }

        .form-group#other-platform {
            display: none;
        }
        .logo {
            margin: 10px 0 0 0;
            display: block;
            margin: 0 auto 15px; /* Center horizontally and add spacing */
            width: 190px; /* Set width */
            height: 100px; /* Set height */
            object-fit: contain; /* Ensure the image scales properly without distortion */
        }
        .password-form {
            background: #1c1c1c;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 8px 15px rgba(0, 0, 0, 0.2);
            max-width: 400px;
            width: 100%;
        }
        .password-form input {
            padding: 12px 10px;
            width: 95%;
            margin: 10px auto;
            border-radius: 5px;
            border: 1px solid #ccc;
        }
        .password-form button {
            padding: 10px;
            width: 100%;
            margin-bottom: 10px;
            border-radius: 5px;
            border: 1px solid #ccc;
        }
    </style>
</head>
<body>
    <?php if (!$showForm): ?>
        <!-- Password Form -->
        <div class="password-form">
            <form method="POST" action="">
                <input type="password" name="password" placeholder="Enter password" required>
                <button type="submit">Submit</button>
            </form>
            <?php if ($errorMessage): ?>
                <p class="error"><?php echo $errorMessage; ?></p>
            <?php endif; ?>
        </div>
    <?php else: ?>
        <div class="form-container">
            <img src="/icon_blur.png" alt="Logo" class="logo">
            <h1>Create Link</h1>
            <form action="submit_form.php" method="POST">
                <div class="form-group">
                    <input type="text" id="name" name="name" required placeholder=" ">
                    <label for="name">Name</label>
                </div>
                <div class="form-group">
                    <select id="platform" name="platform" required>
                        <option value="" disabled selected>Select Platform</option>
                        <option value="Telegram">Telegram</option>
                        <option value="Instagram">Instagram</option>
                        <option value="Facebook">Facebook</option>
                        <option value="Tiktok">Tiktok</option>
                        <option value="Other">Other</option>
                    </select>
                </div>
                <div class="form-group" id="other-platform">
                    <input type="text" id="other" name="other_platform" placeholder=" ">
                    <label for="other">Specify Other Platform</label>
                </div>
                <div class="form-group">
                    <input type="text" id="username" name="username" required placeholder=" ">
                    <label for="username">Username</label>
                </div>
                <div class="form-group">
                    <input type="email" id="email" name="email" required placeholder=" ">
                    <label for="email">Email</label>
                </div>
                <div class="form-group">
                    <textarea id="description" name="description" rows="3" required placeholder="Description"></textarea>
                </div>
                <div class="form-group">
                    <input type="date" id="expiry_date" name="expiry_date" required>
                    <label for="expiry_date">Expiry Date</label>
                </div>
                <div class="form-group">
                    <input type="text" id="pageid" name="pageid" value="<?php echo strtoupper(substr(md5(uniqid()), 0, 15)); ?>">
                    <label for="pageid">Page ID</label>
                </div>
                <button type="submit">Create</button>
            </form>
            <br>
        </div>

        <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.2/gsap.min.js"></script>
        <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
        <!-- Include SweetAlert2 -->
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.0.17/dist/sweetalert2.all.min.js"></script>
        <script>
            $(document).ready(function () {
                $('form').on('submit', function (e) {
                    e.preventDefault(); // Prevent default form submission

                    const formData = $(this).serialize(); // Serialize form data

                    $.ajax({
                        url: '/process_form.php',
                        type: 'POST',
                        data: formData,
                        dataType: 'json',
                        success: function (response) {
                            if (response.status === 'success') {
                                Swal.fire({
                                    title: 'Success!',
                                    text: response.message,
                                    icon: 'success',
                                    confirmButtonText: 'OK'
                                });
                                $('form')[0].reset();
                                $('#other-platform').hide();
                            } else {
                                Swal.fire({
                                    title: 'Error!',
                                    text: response.message,
                                    icon: 'error',
                                    confirmButtonText: 'OK'
                                });
                            }
                        },
                        error: function (xhr, status, error) {
                            console.log(xhr.responseText);
                            Swal.fire({
                                title: 'Error!',
                                text: 'An unexpected error occurred: ' + xhr.responseText,
                                icon: 'error',
                                confirmButtonText: 'OK'
                            });
                        }
                    });             

                });

                // Show/Hide other platform field dynamically
                $('#platform').on('change', function () {
                    if ($(this).val() === 'Other') {
                        $('#other-platform').show();
                    } else {
                        $('#other-platform').hide();
                    }
                });
            });

            gsap.from(".form-container", {
                opacity: 0,
                y: -50,
                duration: 1.5,
                ease: "power2.out"
            });

            gsap.from(".form-group", {
                opacity: 0,
                y: 30,
                stagger: 0.2,
                duration: 1,
                delay: 0.5,
                ease: "power2.out"
            });
        </script>
    <?php endif; ?>
</body>
</html>
