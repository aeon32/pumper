function ControllersManagers(timeout) {
    this.ajaxUrl = $('#basename')[0].value+'ajax.php';
    //this.ajaxUrl = 'ajax.php';
    this.controllers_table_body = $("#controllers_table")[0];
    this.error_message = $("#error_message");


    this.request_success = function (data, textStatus, jqXHR) {
        this.error_message.hide();

    };

    this.request_error = function(data, textStatus, jqXHR)
    {
        var controllersManager = this;
        this.error_message.show();
        setTimeout(function() {controllersManager.updateTable();}, timeout);

    };
      this.updateTable = function () {
        var controllersManager = this;
        $.get({
                url: this.ajaxUrl,
                data: {request: "get_controllers_list"},
                success: function(data, textStatus, jqXHR) {controllersManager.request_success(data, textStatus, jqXHR);},
                error:   function(data, textStatus, jqXHR) {controllersManager.request_error(data, textStatus, jqXHR);},
                timeout:10000
            }
        );
    };

    $(this);

}


$(document).ready(function () {
        timeout = 500;

        this.controllersManager = new ControllersManagers(timeout);

        //alert(test);

        setTimeout(function () {
                this.document.controllersManager.updateTable();
            },
            timeout
        );

    }
);