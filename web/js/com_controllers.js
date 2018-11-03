function error_result(responseXML) {
    var node=responseXML.documentElement;
    if (!node) {
        return 'Ошибка запроса';
    } else {
        if (node.tagName=='error'){
            switch (node.getAttribute('type')) {
                case 'not_authorized':
                    return 'Ошибка авторизации';
                    break;
                default:
                    if (node.firstChild && node.firstChild.data )
                        return node.firstChild.data;
                    else
                        return 'Ошибочный запрос';

            }
        }
    }
}


function ControllersManagers(timeout) {
    this.error_timeout = 10000;
    this.timeout = timeout;
    this.baseName = $('#basename')[0].value;
    this.ajaxUrl = this.baseName +'ajax.php';


    this.controllers_table_body = $("#controllers_table")[0];
    this.error_message = $("#error_message");

    this.updateRowAuxField = function (row)
    {
        if (! ('nameLink' in row ))
        {
            row.numberTD = $(row.cells[0]);
            row.nameLink = $("a", row.cells[1]);

            row.statusTD = $(row.cells[2]);
            row.pressureTD =$(row.cells[3]);
            row.zoneTD = $(row.cells[4]);
        }
    }

    this.controllersTableCreateRow = function(index)
    {
        var row = this.controllers_table_body.insertRow(index);
        var numberTD = row.insertCell(0);
        var nameTD = row.insertCell(1);
        var statusTD = row.insertCell(2);
        var pressureTD = row.insertCell(3);
        var zoneTD = row.insertCell(4);
        numberTD.className = "td_small";
        nameTD.className = "td_big";
        statusTD.className = "td_small";
        pressureTD.className = "td_small";
        zoneTD.className = "td_small";

        var nameLink = document.createElement("a");
        nameTD.appendChild(nameLink);

        row.nameLink = $(nameLink);
        row.numberTD = $(numberTD);
        row.statusTD = $(statusTD);
        row.pressureTD = $(pressureTD);
        row.zoneTD = $(zoneTD);

        return row;


    };

    this.updateTable = function (controllerList)
    {
        var rowCount = this.controllers_table_body.rows.length - 1;

        var expectedRowCount = controllerList.length;

        if (rowCount < expectedRowCount) {
            for (i = 0; i < expectedRowCount - rowCount; i++)
            {
                this.controllersTableCreateRow(-1);
            }
        } else {
            for (i = 0; i < rowCount - expectedRowCount; i++)
            {
                this.controllers_table_body.deleteRow(this.controllers_table_body.rows.length -1);
            }

        };

        for (i = 0; i <expectedRowCount; i++)
        {
            var controller = controllerList[i];
            var row =  this.controllers_table_body.rows[i+1];
            row.className =  i %2 ? "polos_tr" : "";
            this.updateRowAuxField(row);

            var numberTD = row.numberTD;
            var nameLink = row.nameLink;


            $(numberTD).text(i+1);
            nameLink.text(controller.name);
            nameLink[0].href =  "controller/" + controller.id +"/";

            var statusText = "Неизвестен";
            var pressure="";
            var step = "";
            if (controller.online && controller.monitoring_info !==null )
            {
                var monitoring_info = controller.monitoring_info;
                statusText = monitoring_info.is_working ? "В работе" : "Простаивает";
                pressure = controller.monitoring_info.pressure;
                if (monitoring_info.is_working)
                {
                    step = monitoring_info.current_valve.toString() + "/" + monitoring_info.current_step.toString();
                }

            }
            row.statusTD.text(statusText);
            row.pressureTD.text(pressure);
            row.zoneTD.text(step);




        }



    };


    this.request_success = function (jqXHR, textStatus, errorThrown ) {
        var controllersManager = this;
        var error=error_result(jqXHR);
        //alert(responseText);
        if (!error) {
            this.error_message.hide();
            var node=jqXHR.documentElement;
            if (node.tagName == 'controller_list')
            {
                var controller_list = JSON.parse(node.firstChild.data);
                if (Array.isArray(controller_list))
                    controllersManager.updateTable(controller_list);

            }

        } else
        {
            this.error_message.text(error);
            this.error_message.show();

        };

        setTimeout(function() {controllersManager.updateTableRequest();},  error ? controllersManager.error_timeout : controllersManager.timeout );

    };

    this.request_error = function(jqXHR, textStatus, errorThrown)
    {
        var controllersManager = this;
        this.error_message.text("Ошибка обновления информации");
        this.error_message.show();
        setTimeout(function() {controllersManager.updateTableRequest();}, controllersManager.error_timeout);

    };
      this.updateTableRequest = function () {
        var controllersManager = this;
        $.get({
                url: this.ajaxUrl,
                data: {request: "get_controllers_list"},
                dataType: "xml",
                success: function(jqXHR, textStatus, errorThrown) {controllersManager.request_success(jqXHR, textStatus, errorThrown);},
                error:   function(jqXHR, textStatus, errorThrown) {controllersManager.request_error(jqXHR, textStatus, errorThrown);},
                timeout: controllersManager.error_timeout
            }
        );
    };

    $(this);

}


$(document).ready(function () {
        timeout = 1000;

        this.controllersManager = new ControllersManagers(timeout);

        //alert(test);

        setTimeout(function () {
                this.document.controllersManager.updateTableRequest();
            },
            timeout
        );

    }
);