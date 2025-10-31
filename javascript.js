//Show / Hide Password
const forms = document.querySelector('form');
forms.addEventListener("submit", event => {
  if (event.submitter && event.submitter.hasAttribute("formnovalidate")) {
    return;
  }

  if (!forms.checkValidity()) {
    event.preventDefault();
  }
  
  forms.classList.add("was-validated");
});

let togglePassword = document.querySelector('.eye')
let passwordInput = document.getElementById('password') 

togglePassword.addEventListener('click', () => {
    let type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password'
    passwordInput.setAttribute('type', type)
    
    togglePassword.classList.toggle('bi-eye')
    togglePassword.classList.toggle('bi-eye-slash')      
});
    
    