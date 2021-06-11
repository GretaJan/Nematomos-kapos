
var formBtn = document.querySelector('button[type="submit"]');

if(formBtn){
    formBtn.addEventListener("click", function(e){
        e.preventDefault();
        removeErrorFields();
        var form = document.querySelector('form');
        var formObject = form.elements
        var data = {};
        for(let [key, item] of Object.entries(formObject)) {
            let name = item.getAttribute('name');
            if(name){
                data[name] = item.value;
            }
        }
        fetch("smartsheet/registration.php", {
            method: 'POST',
            body: JSON.stringify(data)
        }).then(function(response) {
            if (response.ok) {
                return response.json();
            } else {
                appendGeneralError();
            }
        }).then(function(response){
            if(response.status == '200'){
                appendHtmlField(response.message, 'success-message', formBtn)
                setTimeout(() => {
                    document.querySelector('.success-message').remove();
                }, 10000);
            } else if(response.status == '422'){
                response.errors.forEach((item) => {
                    for(let [key, value] of Object.entries(item)){
                        let inputField = document.querySelector(`form *[name=${key}]`)
                        appendHtmlField(value, 'error-message', inputField)
                    }
                })
            } else {
                appendGeneralError();
            } 
        }).catch(function(){
            appendGeneralError();
        })
    })
    
    function removeErrorFields(){
        document.querySelectorAll('.error-message').forEach(item => {
            item.remove();
        })
    }
    
    function appendGeneralError(){
        let errorMsg = 'Įvyko klaida.';
        appendHtmlField(errorMsg, 'error-message', formBtn)
    }

    function appendHtmlField(message, className, mainField){
        let errorField = document.createElement('div');
        errorField.classList.add(className);
        errorField.innerHTML = `<p>${message}</p>`;
        mainField.parentNode.insertBefore(errorField, mainField);
    }
}
