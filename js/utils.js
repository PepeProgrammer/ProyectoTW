var lastPetitionTime = 0;
function generateDeleteUserModal(id, email) {
    modal = `
    <div class="modal">
        <div class="modal_content">
            <p>¿Seguro que deseas eliminar al usuario ${email}?</p>
            <div>
                <a class="redirection danger" href="users.php?delete=${id}">Eliminar usuario</a>
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


    //ordenación
    let orderby = $('#orderby').val();
    let order = $('#order').val();

    let now = Date.now();
    if (now - lastPetitionTime > 20) { //Con esta línea evitamos que se puedan hacer peticiones continuas al servidor y sobrecargarlo
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
            success: function (bookings) {
                bookings = JSON.parse(bookings);
                let content = "";
                for (let i = 0; i < bookings.length; i++) {
                    let css = i % 2 === 0 ? 'even' : 'odd';
                    let textareaCss = i % 2 === 0 ? 'odd' : 'even';
                    let comments = "";
                    if (bookings[i].comments !== "") {
                        comments = `
                    <h3>Comentarios</h3>
                    <p class="comments">${bookings[i].comments}</p>
`
                    } else {
                        comments = "<p>No hay comentarios</p>";
                    }
                    content += `
                <div class="element">
                            <span class="${css}">${bookings[i].email}</span>
                            <span class="${css}">${bookings[i].room_num}</span>
                            <span class="${css}">${bookings[i].checkin}</span>
                            <span class="${css}">${bookings[i].checkout}</span>
                            <div class="operations ${css}">
                                
                                <img src="../images/edit.png" class="image_button"
                                     alt="icono para editar reserva" onclick="showElement('${bookings[i].comments}','${bookings[i].id}')"/>
          
                                <img src="../images/delete.png" class="image_button" alt="icono para borrar habitacion"
                                     onclick="generateDeleteModal('¿Desea eliminar la reserva de la habitación ${bookings[i].room_num}','checkBooking.php', 'delete', 'Eliminar', ${bookings[i].id})"/>
                            </div>
                                <div id="${bookings[i].id}"
                                 class="container container_five ${css}">
                                    ${comments}
                                    <div class="modifications" id="hidden_${bookings[i].id}" style="display: none;">
                                            <form action="../pages/bookings.php" method="post" novalidate>
                                                <label id="modification_request_text" for="comments">Modificación de comentario:</label>
                                                <textarea name="comments" class="${textareaCss}" id="comments" cols="30" rows="6">${bookings[i].comments}</textarea>
                                                <input id="hidden_id" type="hidden" name="hidden_id" value="">
                                                <div>
                                                    <button type="submit" value="Modificar">Modificar</button>
                                                    <button value="cancelar" onclick="showElement()">Cancelar</button>
                                                </div>
                                            </form>
                                        </div>
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

function showElement(text= "",id= "-1") {
    $('.modifications').hide();
    let hidden = document.getElementById(`hidden_${id}`);
    if (hidden.style.display === "none") {
        hidden.style.display = "block";
    }else{
        hidden.style.display = "none"
    }

    let textarea = document.getElementById("comments");
    textarea.value = text;
}
