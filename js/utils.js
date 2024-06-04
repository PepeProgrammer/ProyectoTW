var lastPetitionTime = 0;
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


function deleteImage(id) {
    $(`#${id}`).remove()
    $('#room_form').append(`<input type="hidden" name="deleted_images[]" value="${id}">`);
}



function searchBookings() {
    let search_text = $('#searcher').val();
    let checkin = $('#checkin').val();
    let checkout = $('#checkout').val();

    console.log(checkout)

    //ordenación
    let orderby = $('#orderby').val();
    let order = $('#order').val();

    let now = Date.now();
    if (now - lastPetitionTime > 100) { //Con esta línea evitamos que se puedan hacer peticiones continuas al servidor y sobrecargarlo
        $.ajax({
            type: 'POST',
            url: '../extra/getBookings.php',
            data: {
                search: search_text,
                checkin: checkin,
                checkout: checkout,
                orderby: orderby,
                order: order
            },
            success: function (response) {
                response = JSON.parse(response);
                let content = "";
                for (let i = 0; i < response.length; i++) {
                    console.log(response[i])
                    let css = i % 2 === 0 ? 'even' : 'odd';
                    let comments = "";
                    if (response[i].comments !== "") {
                        comments = `
                    <h3>Comentarios</h3>
                    <p class="comments">${response[i].comments}</p>
`
                    } else {
                        comments = "<p>No hay comentarios</p>";
                    }
                    content += `
                <div class="element">
                            <span class="${css}">${response[i].email}</span>
                            <span class="${css}">${response[i].room_num}</span>
                            <span class="${css}">${response[i].checkin}</span>
                            <span class="${css}">${response[i].checkout}</span>
                            <div class="operations ${css}">
                                <a href="createRoom.php?id={{ room.id }}">
                                    <img src="../images/edit.png" class="image_button"
                                         alt="icono para editar reserva"/>
                                </a>
                                <img src="../images/delete.png" class="image_button" alt="icono para borrar habitacion"
                                     onclick="generateDeleteModal('¿Desea eliminar la reserva de la habitación ${response[i].room_num}','rooms.php', 'delete', 'Eliminar', ${response[i].id})"/>
                            </div>
                                <div id="${response[i].id}"
                                 class="container container_five ${css}">
                                    ${comments}
                                </div>    
                </div>
                `;
                }

                $('#items').html(content);

            }
        });
        lastPetitionTime = now;
    }
}