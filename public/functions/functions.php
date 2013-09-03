<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * TinyWall
 * @author ArunDavid,MohanKumar,AshokRaj
 * @since Feb,13,2010
 * @version 4
 * @copyright tinywall.com
 */
class TinyWall {
    public $dbLinkConnection = "";
    public $dbSelected = "";
    public $session_user;
    function fnDBCOpen(){
		ini_set('memory_limit', '-1');
        $dbHost='localhost';
        $dbUserName='root';
        $dbPassWord='';
        $dbName = 'tinywall_db';
        $this->dbLinkConnection = mysql_connect($dbHost,$dbUserName,$dbPassWord) or die("Couldn't make connection.");
        $this->dbSelected = mysql_select_db($dbName, $this->dbLinkConnection) or die("Couldn't select database");
    }
	function fnCheckSession(){
        if(!isset($_SESSION['chat_username'])){
            header('Location:'.$_SESSION['base_url'].'login');
            echo "for above redirect";
		}
    }
    function fnDBCClose(){
        mysql_close($this->dbLinkConnection);
    }
 	function fnValidate(){
        if(!isset($_SESSION['chat_username'])){
            header('Location:'.$_SESSION['base_url']."login");
			$notvalid=1;
        }
        $q="SELECT * from users where username='".$_SESSION['chat_username']."'";
        $result=mysql_query($q) or die("Cannot Select Session IDs");
        if(mysql_num_rows($result)==1){
            $row=mysql_fetch_array($result);
            $this->session_user=$row;
        }
    }
}
?>