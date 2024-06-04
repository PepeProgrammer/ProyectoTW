function generateDeleteUserModal(id, email) {
    console.log("hola");
    modal = `
    <div class="modal">
        <div class="modal_content">
            <p>¿Seguro que deseas eliminar al usuario ${email}?</p>
            <div>
                <button class="danger" onclick="window.location.href='users.php?delete=${id}'">Eliminar usuario</button>
                <button onclick="deleteModal()">Cancelar</button>
            </div>
        </div>
    </div>

`
    $('body').append(modal);

}

function deleteModal() {
    $('.modal').remove();
}

function generateDeleteModal(message, url, buttonName, buttonText, id = -1) {
    let value = "";
    if(id !== -1) {
        value = id;
    }
    modal = `
    <div class="modal">
        <div class="modal_content">
            <p>${message}</p>
                <form action="${url}" method="post" novalidate>
                    <button class="danger" type="submit" name="${buttonName}" value="${value}">${buttonText}</button>
                    <button onclick="deleteModal()">Cancelar</button>
                </form>
        </div>
    </div>

`
    $('body').append(modal);
}


function detailsWindow(id) {
    /*Se ha creado este timeout porque se daba el caso que cuando se hacía click en el
    botón de detalles de una habitación, se abría los detalles de la habitación antes de cambiarse de página y así lo evitamos,
    El tiempo del timeout es lo suficientemente breve como para que no se note en la página*/
    setTimeout( () => {
        if($('.modal').length === 0) { // Si no hay un modal abierto se abre la información de la habitación
            $('.container').hide();
            $(`#${id}`).show();
        }
    },50);
}

function showElement(text= "",id= "-1", room = "", email = "") {
    let hidden = document.getElementById("hidden");
    if (hidden.style.display == "none") {
        hidden.style.display = "block"
    }else{
        hidden.style.display = "none"
    }

    let textarea = document.getElementById("comments");
    textarea.value = text;

    let input = document.getElementById("hidden_id");
    input.value = id;

    let label = document.getElementById("modification_request_text");
    label.textContent = "Modificación del comentario del usuario " + email + " para la habitación " + room + ":";
}
