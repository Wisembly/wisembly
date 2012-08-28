<?php

use Silex\Application;

use SilexCMS\Form\TableType;
use SilexCMS\Response\TransientResponse;

use Symfony\Component\HttpFoundation\Request;

$app->match('/administration/{table}', function (Application $app, Request $req, $table) {

    if (is_null($app['security']->getUsername())) {
        return $app->redirect($app['url_generator']->generate('index'));
    }

    $app['db']->query("SET NAMES 'UTF8'");
    $rows = array('rows' => $app['db']->fetchAll("SELECT * FROM `{$table}`"));

    $rows = $rows['rows'];
    foreach ($rows as $row) {
        $data[] = array_map(function($val) {
            return !is_numeric($val) ? substr(strip_tags($val), 0, 47) . '...' : $val;
        }, $row);
    }

    $fields = $app['db']->getSchemaManager()->listTableColumns($table);

    return new TransientResponse($app['twig'], 'administration.html.twig', array('table' => $table, 'fields' => $fields, 'rows' => $data));
})
->bind('administration');

$app->match('/administration/{table}/{id}', function (Application $app, Request $req, $table, $id) {

    if (is_null($app['security']->getUsername())) {
        return $app->redirect($app['url_generator']->generate('index'));
    }

    if (!is_numeric($id)) {
        throw new \Exception("Wrong parameters");
    }

    $app['db']->query("SET NAMES 'UTF8'");
    $row = array('row' => $app['db']->fetchAll("SELECT * FROM `{$table}` WHERE id = {$id}"));
    $rows = array('rows' => $row['row']);
    $form = $app['form.factory']->create(new TableType($app, $table), $rows);

    if ($req->getMethod() === 'POST') {
        $form->bindRequest($req);

        if ($form->isValid()) {
            $data = $form->getData();

            foreach ($data['rows'] as $row) {
                $where = array('id' => $row['id']);
                unset($row['id']);
                $app['db']->update($table, $row, $where);
            }
        }
    }

    return new TransientResponse($app['twig'], 'administration_edit.html.twig', array(
        'table' => $table,
        'id'    => $id,
        'form'  => $form->createView()
    ));
})
->bind('administration_edit');

$app->match('/administration', function(Application $app, Request $req) {

    if (is_null($app['security']->getUsername())) {
        return $app->redirect($app['url_generator']->generate('index'));
    }

    return new TransientResponse($app['twig'], 'administration_hub.html.twig');
})
->bind('administration_hub');