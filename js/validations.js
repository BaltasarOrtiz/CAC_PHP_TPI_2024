document.getElementById("formInicioSesion").addEventListener("submit",function(event)
{
    event.preventDefault();

    var email=document.getElementById("email").value;
    var password=document.getElementById("password").value;
    var emailError=document.getElementById("emailError");
    var passwordError=document.getElementById("passwordError");
    var regexEmail=/^[^\s@]+@[^\s@]+\.[^\s@]+$/;

    emailError.textContent="";
    passwordError.textContent="";

    

    if(email.trim()==="")
        {
            emailError.textContent="El campo 'Correo electrónico' no puede estar vacío";
            console.log("El campo 'Correo electrónico' no puede estar vacío")
            return;
        }

    if(!regexEmail.test(email))
        {
            emailError.textContent="El correo electrónico está mal escrito";
            console.log("El correo electrónico está mal escrito")
            return;
        }

    if(password.trim()==="")
        {
            passwordError.textContent="El campo 'Contraseña' no puede estar vacío";
            console.log("El campo 'Contraseña' no puede estar vacío")
            return;
        }

        alert("¡Inicio de sesión exitoso!")
        console.log("inicio de sesión exitoso")
});