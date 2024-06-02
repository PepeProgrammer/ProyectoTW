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

function generateDeleteModal(message, url, buttonName, buttonText) {
    console.log("hola");
    modal = `
    <div class="modal">
        <div class="modal_content">
            <p>${message}</p>
                <form action="${url}" method="post" novalidate>
                    <button class="danger" type="submit" name="${buttonName}">${buttonText}</button>
                    <button onclick="deleteModal()">Cancelar</button>
                </form>
        </div>
    </div>

`
    $('body').append(modal);

}


function detailsWindow(id) {
    $('.container').hide();
    $(`#${id}`).show();
}
