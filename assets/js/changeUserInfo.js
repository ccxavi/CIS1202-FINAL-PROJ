document.querySelector('.modal-footer .btn-primary').addEventListener('click', function () {
  const profilePicInput = document.getElementById('profilePicUpload');
  const usernameInput = document.getElementById('username');
  const emailInput = document.getElementById('email');
  const currentPassword = document.getElementById('currentPassword');
  const newPassword = document.getElementById('newPassword');
  const confirmPassword = document.getElementById('confirmPassword');

  const isProfilePicChanged = profilePicInput.files.length > 0;
  const isAccountInfoChanged = usernameInput.value.trim() !== "" || emailInput.value.trim() !== "";
  const isPasswordChanged = currentPassword.value !== "" || newPassword.value !== "" || confirmPassword.value !== "";

  let formSubmitted = false;
  let formPromises = [];

  // Validate password change
  if (isPasswordChanged) {
      if (newPassword.value !== confirmPassword.value) {
          document.getElementById('passwordMatchError').textContent = "Passwords do not match.";
          return;
      }
      const passwordFormData = new FormData(document.getElementById('passwordForm'));
      formPromises.push(fetch(document.getElementById('passwordForm').action, {
          method: 'POST',
          body: passwordFormData
      }));
  }

  // Submit profile picture form
  if (isProfilePicChanged) {
      const profilePicFormData = new FormData(document.getElementById('profilePicForm'));
      formPromises.push(fetch(document.getElementById('profilePicForm').action, {
          method: 'POST',
          body: profilePicFormData
      }));
  }

  // Submit account info form
  if (isAccountInfoChanged) {
      const accountInfoFormData = new FormData(document.getElementById('accountInfoForm'));
      formPromises.push(fetch(document.getElementById('accountInfoForm').action, {
          method: 'POST',
          body: accountInfoFormData
      }));
  }

  // Wait for all submissions to finish
  Promise.all(formPromises)
      .then(() => {
          location.reload();  // Reload the page after all forms are processed
      })
      .catch(error => {
          console.error('Error during form submission:', error);
      });
});

const uploadInput = document.getElementById('profilePicUpload');
    const previewImg = document.getElementById('profilePicPreview');

    uploadInput.addEventListener('change', function () {
        const file = this.files[0];
        if (file && file.type.startsWith('image/')) {
            const reader = new FileReader();
            reader.onload = function (e) {
                previewImg.src = e.target.result;
            };
            reader.readAsDataURL(file);
        }
    });