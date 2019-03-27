<?php

require_once(PATH_BASE . '/framework/abstractcomponent.php');
require_once(PATH_BASE . '/framework/utils.php');

class com_controller_edit extends AbstractComponent
{
    //варианты режима работы

    const MODE_NOT_FOUND = 1;
    const MODE_SHOW = 2;


    private $limit_random = 20;
    private $site = NULL;
    private $controller_manager = NULL;
    private $controller = NULL;
    private $mode;

    public function __construct(CSite $site)
    {
        $this->site = $site;
        $this->controller_manager = $site->getControllersManager();
        $user = $site->getUser();
        if (!$user->getAuthorized())
            die();
        $id = array_key_exists('id', $_GET) ? $_GET['id'] : NULL;

        $this->mode = self::MODE_NOT_FOUND;

        if (is_numeric($id)) {
            $id = (int)$id;
            $this->controller = $this->controller_manager->getController($id);
            if (!$this->controller) {
                $this->mode = self::MODE_NOT_FOUND;
            } else {
                $this->mode = self::MODE_SHOW;
            };
        } else {
            $this->mode = self::MODE_NOT_FOUND;
        };
    }


    public function getTitle()
    {
        switch ($this->mode) {
            case self::MODE_NOT_FOUND:
                return "Страница не найдена";
                break;
            case self::MODE_SHOW:
                return "Управление контроллером";
                break;

        };

    }

    /**
     * Функция возвращает ключевые слова (заголовок для страницы)
     * @return  string заголовок страницы
     */
    public function getKeyWords()
    {
        return "Контроллер продувки";
    }

    /**
     * Функция возвращает description(заголовок для страницы)
     * @return  string заголовок страницы
     */
    public function getDescription()
    {
        return $this->getTitle();
    }


    public function getCSSFile()
    {
        return "new_main.css";
    }

    private function renderForm()
    {
        ?>
        <?php
         /*if ($this->mode == self::MODE_SAVE)
        print("<h4>Cхема сохранена</h4>"); */
        ?>

        <div class="content">
            <div class="header_div">
                <h1>Управление контроллером</h1>
            </div>
            <div id="error_message" style="display:none">Ошибка обновления информации</div>

            <table id="pumping_table" class="list_table" width="100%">
                <tbody>
                <tr>
                    <th class="td_small">Номер шага</th>
                    <th class="td_small">Номер клапана</th>
                    <th class="td_small">Время продувки</th>
                </tr>
                </tbody>
            </table>

            <form name="admin_table" id="admin_table" method="post" action="">
                <input type="hidden" id="basename" value="<?php print($this->site->getBaseName()); ?>"/>
                <input type="hidden" id="controller_id" value="<?php print(is_object($this->controller) ? $this->controller->id : "null")?>" />
                <input id="refresh_settings_button" value="Обновить" type="button"/>
            </form>

            <?php $this->renderControllerEditor(); ?>

        </div>
        <?php
    }

    public function renderControllerEditor()
    {
       // require_once(PATH_BASE . '/components/controller_editor/controller_editor.php');
       // renderControllerEditor();
    }

    public function render()
    {
        switch ($this->mode) {
            case self::MODE_NOT_FOUND:
                $this->renderNotFound();
                break;
            case self::MODE_SHOW:
                $this->renderForm();
                break;
            default:
                $this->renderForm();
        };
        ?>

        <?php
    }

    public function renderDelete()
    {
        ?>
        <div class="content">
            <div class="header_div">
             <h1>Управление контроллером</h1>
            </div>
         <div id="error_message">Страница не найдена</div>
        </div>
        <?php
    }

    public function getAuxScripts()
    {
        return array('js/com_controller_edit.js');
    }

    public function save()
    {

    }

    public function renderNotFound()
    {
        ?>
        <div class="content">
            <div class="header_div">
                <h1>Управление контроллером</h1>
            </div>
            <div id="error_message">Страница не найдена</div>
        </div>
        <?php
    }
}

?>
