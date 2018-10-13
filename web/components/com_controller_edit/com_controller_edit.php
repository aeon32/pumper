<?php

require_once(PATH_BASE . '/framework/abstractcomponent.php');
require_once(PATH_BASE . '/framework/utils.php');

class com_controller_edit extends AbstractComponent
{
    //варианты режима работы
    const MODE_CREATE_NEW = 1;
    const MODE_EDIT_NEW = 2;
    const MODE_NOT_FOUND = 3;
    const MODE_SHOW = 4;
    const MODE_SAVE = 5;
    const MODE_DELETE = 6;
    const MODE_UPLOAD = 7;

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
        if ($site->getAction() == 'save') {
            /*
         $id = (int) $_POST["scheme_id"];
            $name = processMagicQuotes($_POST['name']);

      $this->scheme = new Scheme($id,$name);
      $this->scheme = $this->scheme_manager->save($this->scheme);
      if (isset($this->scheme)) {
          $id = $this->scheme->id;
      } else {
          $id = null;
      };
      $this->mode = self::MODE_SAVE;
      */
        } else if ($site->getAction() == 'delete') {


            $id = (int)$id;
            $deleted = $this->scheme_manager->deleteScheme($id);
            $this->mode = $deleted > 0 ? self::MODE_DELETE : self::MODE_NOT_FOUND;


        } else if ($site->getAction() == 'new') {
            $this->scheme = $this->scheme_manager->createTemporary();
            $this->mode = self::MODE_CREATE_NEW;
        } else if (is_numeric($id)) {
            $id = (int)$id;
            $this->controller = $this->controller_manager->getController($id);
            if (!$this->controller) {
                $this->mode = self::MODE_NOT_FOUND;
            } else if ($this->scheme->temporary) {
                $this->mode = self::MODE_EDIT_NEW;
            } else {
                $this->mode = self::MODE_SHOW;
            };
            if ($this->mode != self::MODE_NOT_FOUND && $site->getAction() == 'upload_map') {
                $this->mode = $this->controller_manager->uploadMap($id) ? self::MODE_UPLOAD : self::MODE_UPLOAD_ERROR;
            };
        } else {
            $this->mode = self::MODE_NOT_FOUND;
        };
    }


    public function getTitle()
    {
        switch ($this->mode) {
            case self::MODE_CREATE_NEW:
                return "Создание нового контроллера";
                break;
            case self::MODE_EDIT_NEW:
                return "Создание схемы расположения точек wifi";
                break;
            case self::MODE_NOT_FOUND:
                return "Страница не найдена";
                break;
            case self::MODE_SHOW:
                return "Управление контроллером";
                break;
            case self::MODE_DELETE:
                return "Страница удалена";
                break;
            case self::MODE_UPLOAD:
                return "Схема размещения точек wifi";
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
        <?php if ($this->mode == self::MODE_SAVE)
        print("<h4>Cхема сохранена</h4>");
        ?>

        <div class="content">
            <div class="header_div">
                <h1>Управление контроллером</h1>
            </div>
            <div id="error_message">Ошибка обновления информации</div>

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
            case self::MODE_CREATE_NEW:
                $this->renderForm();
                break;
            case self::MODE_EDIT_NEW:
                $this->renderForm();
                break;
            case self::MODE_NOT_FOUND:
                $this->renderNotFound();
                break;
            case self::MODE_SHOW:
                $this->renderForm();
                break;
            case self::MODE_SAVE:
                $this->renderForm();
                break;
            case self::MODE_DELETE:
                $this->renderDelete();
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
        <div id="tabl">
            <h1>Редактирование схемы</h1>
        </div>
        <div>
            <h2>Схема удалена</h2>
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
        <div id="tabl">
            <h1>Редактирование схемы</h1>
        </div>
        <div>
            <h2>Страница не найдена</h2>
        </div>
        <?php
    }
}

?>
