<?php

/*Функция рисует редактор точек доступ */
function renderControllerEditor()
{
    //require_once (PATH_BASE.'/framework/access_point_settings.php') ;
    ?>
    <div id="access_point_editor">
        <form id="access_point_editor_form">
            <h2>Общие параметры точки:</h2>

            <p class="label_input_wrapper">
                <label for="access_point_name">Наименование:</label><input name="name" id="access_point_name"
                                                                           type="text">
            </p>
            <p class="label_input_wrapper">
                <label for="ip_address">IP-адрес:</label><input name="ip_address" id="ip_address" type="text">
            </p>
            <p class="label_input_wrapper">
                <label for="community">Строка community:</label><input name="community" id="community" type="text">
            </p>
            <p id="buttons_wrapper_point">
                <input id="save_editor_button" value="Сохранить" type="button"/>
                <input id="test_editor_button" value="Тестировать" type="button"/>
                <input id="reload_editor_button" value="Перезагрузить" type="button"/>
            </p>
            <div id="ssid_wrapper">
                <h2>SSID:</h2>
                <div id="ssid_list_wrapper">
                    <ul id="ssid_list">
                        <li class="active"><a href="#">tlf</a></li>
                        <li><a href="#">video</a></li>
                    </ul>
                    <div>
                        <input id="add_ssid_button" value="+" type="button"/>
                        <input id="remove_ssid_button" value="-" type="button"/>
                    </div>
                </div>

                <div id="ssid_info">
                    <p>
                        <label for="ssid_name">SSID:</label>
                        <input name="ssid_name" id="ssid_name" type="text"/>
                    </p>
                    <!--
                    <p>
                        <label for="encryption_type">Тип шифрования:</label>
                        <select name="encryption_type" id="encryption_type" size="1">
                            <option selected="selected" value="no_encrypt">Без шифрования</option>
                            <option value="static_wep">Static WEP</option>
                            <option value="wpa-psk">WPA-PSK/WPA2-PSK</option>
                            <option value="wpa/wpa2">WPA/WPA2</option>
                        </select>
                    </p>
                    -->
                    <p>
                        <label for="ssid_channel">Номер интерфейса:</label>
                        <select name="ssid_channel" id="ssid_channel" size="1">
                            <option selected="selected" value="2457">Channel 10-2457MHz</option>
                            <option value="2458">Channel 11-2458MHz</option>
                        </select>
                    </p>
                    <p>
                        <label for="hidden">Hidden</label><input name="hidden" id="hidden" type="checkbox">
                    </p>
                </div>
            </div>
            <div id="channel_wrapper">
                <h2>Параметры физического интерфейса:</h2>
                <p>
                    <label for="channel_number">Номер интерфейса:</label>
                    <select name="channel_number" id="channel_number" size="1">
                        <option selected="selected" value="1">1</option>
                    </select>

                </p>
                <p>
                    <label for="protocol_type">Тип протокола:</label>
                    <select name="protocol_type" id="protocol_type" size="1">
                        <?php
                        /*

                            foreach (ApChannel::$modes as $id=>$value) {
                                print("<option value=\"$id\">".htmlspecialchars($value)."</option>");
                            };
                        */

                        ?>
                    </select>

                </p>
                <p>
                    <label for="tx_power">Управление мощностью:</label><input name="tx_power" id="tx_power" type="text">
                </p>
                <p>
                    <label for="frequency">Установка частоты:</label><input name="frequency" id="frequency" type="text">
                </p>
                <p>
                    <label for="distance">Управление дальностями:</label><input name="distance" id="distance"
                                                                                type="text">
                </p>
                <p>
                    <label for="rts_threshold">Порог RTS:</label><input name="rts_threshold" id="rts_threshold"
                                                                        type="text">
                </p>

            </div>
            <div id="loading_wrapper">
                <p>Загрузка...</p>
            </div>
            <div id="settings_undefined_wrapper" style="text-align: center">
                <h2>Текущие настройки неизвестны</h2>
                <p>
                    Текущие настройки точки доступа неизвестны; для загрузки настроек нажмите "Обновить"
                </p>
            </div>
            <p id="buttons_wrapper_ssid">
                <input id="refresh_settings_button" value="Обновить" type="button">
                <input id="set_settings_button" value="Настроить" type="button">
            </p>

            <h2>&nbsp;</h2>
            <p>
                <input id="close_editor_button" value="Закрыть" type="button">
            </p>
        </form>
    </div><!-- id="access_point_editor"-->

    <?php
};
?>