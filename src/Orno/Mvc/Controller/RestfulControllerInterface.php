<?php
/**
 * The Orno Component Library
 *
 * @author  Phil Bennett @philipobenito
 * @license http://www.wtfpl.net/txt/copying/ WTFPL
 */
namespace Orno\Mvc\Controller;

/**
 * Restful Controller Interface
 *
 * Contract to ease creation of RESTful controllers
 */
interface RestfulControllerInterface
{
    /**
     * Restful Get All Action
     *
     * GET /controller
     */
    public function getAll();

    /**
     * Restful Get One Action
     *
     * GET /controller/(id)
     *
     * @param mixed $id
     */
    public function get($id);

    /**
     * Restful Create Action
     *
     * POST /controller
     */
    public function create();

    /**
     * Restful Update Action
     *
     * PUT /controller/(id)
     * or
     * PATCH /controler/(id)
     *
     * @param mixed $id
     */
    public function update($id);

    /**
     * Restful Delete Action
     *
     * DELETE /controller/(id)
     *
     * @param mixed $id
     */
    public function delete($id);
}
