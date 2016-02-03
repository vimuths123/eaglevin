<?php
namespace Eagle\AdminBundle\Services;

class Update{
    public $table;
    public $id;
    public $fields;


    public function __construct($table) {
        $this->table = $table;
    }

    public function getTable() {
        return $this->table;
    }
    
    public function setId($id) {
        $this->id = $id;
    }
    
    public function setFields($fields) {
        $this->fields = $fields;
    }
    
    public function execute() {
        return $this->fields;
//        return $this->table ." + ". $this->id;
    }
}
