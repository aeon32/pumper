

function ControllerEdit(timeout) {
    this.error_timeout = 10000;
    this.timeout = timeout;
    this.baseName = $('#basename')[0].value;
    this.ajaxUrl = this.baseName + 'ajax.php';
    this.controllerId = $('#controller_id')[0].value;
    this.error_message = $("#error_message");
    this.pumping_table_body = $("#pumping_table")[0];

    this.error_request_result = function (responseXML) {
        var node = responseXML.documentElement;
        if (!node) {
            return 'Ошибка запроса';
        } else {
            if (node.tagName == 'error') {
                switch (node.getAttribute('type')) {
                    case 'not_authorized':
                        return 'Ошибка авторизации';
                        break;
                    case 'controller_not_found':
                        return "Запись о контроллере отсутствует";
                    case 'controller_is_offline':
                        return "Связь с контроллером отсутствует";
                    case 'controller_not_responds':
                        return "Контроллер не отвечает";
                    default:
                        if (node.firstChild && node.firstChild.data)
                            return node.firstChild.data;
                        else
                            return 'Ошибочный запрос';

                }
            }
        }

    }

    this.updateRowAuxField = function (row)
    {
        if (! ('stepTD' in row ))
        {
            row.stepTD = $(row.cells[0]);
            row.valveTD = $(row.cells[1]);

            row.timeTD = $(row.cells[2]);
        }
    }

    this.pumpingTableCreateRow = function(index)
    {
        var row = this.pumping_table_body.insertRow(index);
        var stepTD = row.insertCell(0);
        var valveTD = row.insertCell(1);
        var timeTD = row.insertCell(2);

        stepTD.className = "td_small";
        valveTD.className = "td_small";
        timeTD.className = "td_small";

        row.stepTD = $(stepTD);
        row.valveTD = $(valveTD);
        row.timeTD = $(timeTD);

        return row;

    };


    this.updateTable = function (fullInfo)
    {
        var rowCount = this.pumping_table_body.rows.length - 1;
        var pumpingTableData = fullInfo.pumping_table;

        var expectedRowCount = pumpingTableData.length;

        if (rowCount < expectedRowCount) {
            for (i = 0; i < expectedRowCount - rowCount; i++)
            {
                this.pumpingTableCreateRow(-1);
            }
        } else {
            for (i = 0; i < rowCount - expectedRowCount; i++)
            {
                this.pumping_table_body.deleteRow(this.pumping_table_body.rows.length -1);
            }

        };

        for (i = 0; i <expectedRowCount; i++) {
            var pumpingStep = pumpingTableData[i];
            var row = this.pumping_table_body.rows[i + 1];
            row.className = i % 2 ? "polos_tr" : "";
            this.updateRowAuxField(row);

            var stepTD = row.stepTD;
            var valveTD = row.valveTD;
            var timeTD = row.timeTD;

            stepTD.text(i + 1);
            valveTD.text(pumpingStep.valve_number);
            timeTD.text(pumpingStep.time_to_run);
        };

    }

    this.request_success = function (jqXHR, textStatus, errorThrown )
    {
        var error=this.error_request_result(jqXHR);
        var controllerEditor = this;
        if (!error) {
            this.error_message.hide();
            var node=jqXHR.documentElement;

            var controller_info = JSON.parse(node.firstChild.data);
            controllerEditor.updateTable(controller_info);


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
        controllerEditor.error_message.hide();
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

    controllerEdit.updateInfoRequest();





    }
);