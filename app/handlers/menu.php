<?php
    $menu = array(
        array('url' => 'photoshop', 'label' => 'Взрослые растения'),
        array('url' => 'index', 'label' => 'Семена и сеянцы'),
        array('url' => 'scart', 'label' => 'Корзинка'),
        array('url' => 'ckout', 'label' => 'Заказ'),
        array('url' => 'photoalbumlist', 'label' => 'Фотогалерея'),
        array('url' => 'contacts', 'label' => 'Контакты'),
    );
    echo json_encode($menu);
//print_r($menu);