<?php 

interface ITxTransaction{

	function begin();

	function commit();

	function rollback();

}