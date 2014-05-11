<?php
    $menu = array(
        array('url' => 'main', 'label' => 'Главная страница'),
        array('url' => 'index', 'label' => 'Семена и сеянцы'),
        array('url' => 'scart', 'label' => 'Корзинка'),
        array('url' => 'ckout', 'label' => 'Заказ'),
        array('url' => 'photoalbumlist', 'label' => 'Фотогалерея'),
        array('url' => 'contacts', 'label' => 'Контакты'),
    );
    echo json_encode($menu);
todo: move to client
//print_r($menu);