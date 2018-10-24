<?php 

interface ITxTransaction{

	function begin();

	function waitCommit($localTransaction);

}