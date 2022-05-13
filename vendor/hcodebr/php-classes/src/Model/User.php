<?php

namespace Hcode\Model;

use \Hcode\DB\Sql;
use Hcode\Mailer;
use \Hcode\Model\Model;

class User extends Model
{ //extendendo a classe model para gerar o getters e setters

    const SESSION = "User";
    const SECRET = "HcodePhp7_Secret"; //16 caracteres

    public static function login($deslogin, $despassword)
    {
        $sql = new Sql(); // instaciando o banco de dados; use \Hcode\DB\Sql;

        //chamando o metodo de select que está dentro da classe SQL, para podere selecionar o que precisa para validar a senha e o usuario
        //e passando o dentro do array a senha que o usuario enviou pelo o formulario.
        $results = $sql->select("SELECT * FROM tb_users WHERE deslogin = :deslogin", array(
            ":deslogin" => $deslogin
        ));

        if (count($results) === 0) { //verificando se encontrou algum resultado dentro do banco de dados.
            //caso não encontre um registro estoura um erro.
            throw new \Exception("Usuário ou Senha Inexistentes");
        }

        //$data está recebendo os DADOS que está na posição 0 dentro do array da variavel $results que veio do banco.
        $data = $results[0];

        //se o hash da senha que está gravada no banco for igual a senha que veio do formulario que o usuario digitou.
        if (password_verify($despassword, $data['despassword']) === true) {

            $user = new User(); //instâcia dessa própria classe.

            //$user->setIdUser($data['iduser']);//passando o método a ser usado. set ou get

            //passando o método a ser usado, set ou get e passando um array de informações que ele encontrou na variavel $results acima.
            $user->setData($data);

            $_SESSION[User::SESSION] = $user->getValues(); //colocando todos os dados do banco de dados dentro da $_SESSION[];
            return $user;
        } else {
            throw new \Exception("Usuário ou Senha Inexistentes");
        }
    }

    public static function verifyLogin($inadmin = true)
    {
        if (
            !isset($_SESSION[User::SESSION]) //verifica se NÃO existe a SESSION com a CONSTANTE SESSION
            ||
            !$_SESSION[User::SESSION] //verifica se a $_SESSION está vazia
            ||
            !(int)$_SESSION[User::SESSION]['iduser'] > 0 //verifica se o iduser que está dentro da SESSION NÃO for maior que 0, se for > 0 realmente existe um USUÁRIO
            ||
            (bool)$_SESSION[User::SESSION]['inadmin'] !== $inadmin
        ) {
            header('location: /admin/login');
            exit;
        }
    }

    public static function logout()
    {
        $_SESSION[User::SESSION] = null;
    }

    public static function listAll()
    {
        $sql = new Sql();

        return $sql->select("SELECT * FROM tb_users a INNER JOIN tb_persons b USING(idperson) ORDER BY b.desperson");
    }

    public function save()
    {
        $sql = new Sql();

        $results = $sql->select("CALL sp_users_save(:desperson, :deslogin, :despassword, :desemail, :nrphone, :inadmin)", 
        array(
            ':desperson'    => $this->getdesperson(),
            ':deslogin'     => $this->getdeslogin(),
            ':despassword'  => $this->getdespassword(),
            ':desemail'     => $this->getdesemail(),
            ':nrphone'      => $this->getnrphone(),
            ':inadmin'      => $this->getinadmin()
        ));
        $this->setData($results[0]);
    }

    public function get($iduser)
    {
        $sql = new Sql();

        $results = $sql->select("SELECT * FROM tb_users a INNER JOIN tb_persons b USING(idperson) WHERE a.iduser = :iduser", array(
			":iduser" => $iduser
		));

        $this->setData($results[0]);
    }

    public function update()
    {
        $sql = new Sql();
        
        $results = $sql->select("CALL sp_usersupdate_save(:iduser, :desperson, :deslogin, :despassword, :desemail, :nrphone, :inadmin)", 
        array(
            ':iduser'       => $this->getiduser(),
            ':desperson'    => $this->getdesperson(),
            ':deslogin'     => $this->getdeslogin(),
            ':despassword'  => $this->getdespassword(),
            ':desemail'     => $this->getdesemail(),
            ':nrphone'      => $this->getnrphone(),
            ':inadmin'      => $this->getinadmin()
        )); 
        $this->setData($results[0]);
    }

    public function delete()
    {
        $sql = new Sql();

        $sql->query("CALL sp_users_delete(:iduser)", array(
            ":iduser" => $this->getiduser()
        ));
    }

    public static function getForgot($email){
        $sql = new SQL();

        $results = $sql->select(
            "SELECT * FROM tb_persons a INNER JOIN tb_users b USING(idperson) WHERE a.desemail = :email", array(
                ':email' => $email
            )
        );

        if (count($results) === 0) {
            throw new \Exception('Não foi possível recuperar a senha!');

        }else{
            $data = $results[0];
            $results2 = $sql->select("CALL sp_userspasswordsrecoveries_create(:iduser, :desip)", array(
                ':iduser' => $data['iduser'],
                ':desip'  => $data['desip']
            ));

            if(count($results2) === 0) {
                throw new \Exception('Não foi possível recuperar a senha!');
            }else{
                
                $dataRecovery = $results2[0];

                $code = base64_encode(openssl_encrypt(MCRYPT_RIJNDAEL_256, "aes-128-gcm", User::SECRET, $dataRecovery["idrecovery"]));

                $link = "http://www.hcodecommerce.com.br/admin/forgot/reset?code=$code";

                $mailer = new Mailer($data['desemail'], $data['desperson'], "Redefir Senha da Hcode Store", "forgot", array(
                    "name" => $data['desperson'],
                    "link" => $link
                ));

                $mailer->send();

                return $data;
            }
        }
    }

    public static function validForgotDecrypt($code){
        $idrecovery  = openssl_decrypt(MCRYPT_RIJNDAEL_256, User::SECRET, base64_decode($code));

        $sql = new Sql();

        $results = $sql->select(
            "SELECT * FROM tb_userspasswordsrecoveries a
            INNER JOIN tb_users b USING(iduser)
            INNER JOIN tb_persons c USING(idperson)
            WHERE a.idrecovery = :idrecovery AND
            a.dtrecovery IS NULL AND
            DATE_ADD(a.dtregister, INTERVAL 1 HOUR) >= NOW()",
            array(
                ":idrecovery" => $idrecovery
            )
        );

        if (count($results) ===0){
            throw new \Exception ("Não foi possível recuperar a senha!");
        } else {
            return $results[0];
        }
    }

    public static function setForgotUsed($idrecovery){
        $sql = new Sql();

        $sql->query("UPDATE tb_userspasswordsrecoveries SET dtrecovery = NOW() WHERE idrecovery = :idrecovery", array(':idrecovery' => $idrecovery));
    }

    public function setPassword($password){
        $sql = new Sql();

        $sql->query("UPDATE tb_users SET despassword = :despassword WHERE iduser = :iduser", 
        array(
            ':despassword' => $password,
            ':iduser' => $this->getiduser()
        ));
    }
}
