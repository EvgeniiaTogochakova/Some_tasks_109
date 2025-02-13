@extends('layouts/default')

@section('content')
<!-- First: the checkbox input -->
<input type="checkbox" id="modal-toggle" checked>

<!-- Second: the modal -->
<div id="modal">
    <div class="modal-window">
        <div class="scroll-bar">
            <h2>Закрывая это окно и&nbsp;начиная чтение рекомендаций, вы&nbsp;подтверждаете, что являетесь специалистом в&nbsp;области здравоохранения. Приятного чтения!</h2>
        </div>
        <!-- Important: this label must be for="modal-toggle" -->
        <label for="modal-toggle" class="close-button">X</label>
    </div>
</div>

        
<div class="faq-container" id="faqContainer"></div>


@stop





