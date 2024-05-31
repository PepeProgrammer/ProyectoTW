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