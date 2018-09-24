<?php
 /**
  * Класс-абстрактный компонент
  * 
  */  
  abstract class AbstractComponent {

  	/**
  	 * Функция вызывается для рендеринга компонента
  	 * 
  	 */
  	abstract public function render();
  	
  	
  	/**
  	 * Функция возвращает title (заголовок для страницы)
  	 * @return  string заголовок страницы
  	 */
  	abstract public function getTitle();
  	
  	
    /**
  	 * Функция возвращает ключевые слова (заголовок для страницы)
  	 * @return  string заголовок страницы
  	 */
  	public function getKeyWords() {
  	  return '';	
  	}
  	
   /**
  	 * Функция возвращает description(заголовок для страницы)
  	 * @return  string заголовок страницы
  	 */
  	public function getDescription() {
  	  return '';	
  	}
  	
  	
  	
  	/**
  	 * Функция возвращает имя css-файла для рендеринга содержимого
  	 */
  	abstract public function getCSSFile(); 
  	
  	/**
  	 * Функция возвращает ключевые слова и т.д сайта
  	 */
    
  	/**
  	 * Функция возвращает массив вспомогательных имён js-скриптов
  	 */
  	abstract public function getAuxScripts();
  }

?>