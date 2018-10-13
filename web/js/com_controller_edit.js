

function ControllerEdit(timeout) {
    this.error_timeout = 10000;
    this.timeout = timeout;
    this.baseName = $('#basename')[0].value;
    this.ajaxUrl = this.baseName +'ajax.php';
    this.controllerId = $('#controller_id')[0].value;
    this.error_message = $("#error_message");

    this.error_request_result = function (responseXML)
    {
        var node=responseXML.documentElement;
        if (!node)
        {
            return 'Ошибка запроса';
        } else {
            if (node.tagName=='error')
            {
                switch (node.getAttribute('type'))
                {
                    case 'not_authorized':
                        return 'Ошибка авторизации';
                        break;
                    case 'controller_not_found':
                        return "Запись о контроллере отсутствует";
                    case 'controller_is_offline':
                        return "Связь с контроллером отсутствует";
                    default:
                        if (node.firstChild && node.firstChild.data )
                            return node.firstChild.data;
                        else
                            return 'Ошибочный запрос';

                }
            }
        }

    }

    this.request_success = function (jqXHR, textStatus, errorThrown )
    {
        var error=this.error_request_result(jqXHR);
        //alert(responseText);
        if (!error) {
            this.error_message.hide();
            var node=jqXHR.documentElement;

        } else
        {
            this.error_message.text(error);
            this.error_message.show();

        }

    };

    this.request_error = function(jqXHR, textStatus, errorThrown)
    {
        this.error_message.text("Ошибка обновления информации");
        this.error_message.show();
    };




    this.updateInfoRequest = function () {
        var controllerEditor = this;
        $.get({
                url: this.ajaxUrl,
                data: {request: "get_controller_info", controller_id: controllerEditor.controllerId},
                dataType: "xml",
                success: function(jqXHR, textStatus, errorThrown) {controllerEditor.request_success(jqXHR, textStatus, errorThrown);},
                error:   function(jqXHR, textStatus, errorThrown) {controllerEditor.request_error(jqXHR, textStatus, errorThrown);},
                timeout: controllerEditor.error_timeout
            }
        );
    };




    $(this);

}





$(document).ready(function () {
    //$('#access_point_editor').show();
    var timeout = 1000;

    this.controllerEdit = new ControllerEdit(timeout);

    var controllerEdit = this.controllerEdit;

    var button = $('#refresh_settings_button')[0];

    button.onclick = function()
    {
        controllerEdit.updateInfoRequest();

    }




    }
);