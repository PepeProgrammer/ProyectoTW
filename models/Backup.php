<?php
require_once "Database.php";
class DBException extends Exception
{
    public function __construct($message = null, $code = null)
    {
        $this->message = $message;
        $this->code = $code;
    }
}
class Backup
{
    protected $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    // Devuelve una cadena con las cláusulas SQL que permiten hacer una restauración completa de la BBDD actual
    public function obtain(): string
    {
        // Obtener listado de tablas
        $result = $this->db->query('SHOW TABLES');
        $tablas = $result->fetch_all(MYSQLI_NUM);

        // Salvar cada tabla
        $salida = '';
        foreach ($tablas as $tab) {
            $result2 = $this->db->query('SELECT * FROM ' . $tab[0]);
            $rows = $result2->fetch_all(MYSQLI_NUM);
            $salida .= 'DROP TABLE IF EXISTS ' . $tab[0] . ';';
            $result2 = $this->db->query('SHOW CREATE TABLE ' . $tab[0]);
            $ctable = strval($result2->fetch_array()[1]);
            $salida .= "\n\n" . $ctable . ";\n\n";
            // Crear cláusula INSERT INTO para cada tupla
            foreach ($rows as $row) {
                $salida .= 'INSERT INTO ' . $tab[0] . ' VALUES(';
                // Concatenar lista de valores en formato SQL
                $insertvalues = '';
                foreach ($row as $k => $v) {
                    if (!is_null($v)) {
                        $aux1 = addslashes($v);
                        $aux2 = preg_replace("/\n/", "\\n", $aux1);
                        if (isset($aux2))
                            $insertvalues .= '"' . $aux2 . '"';
                        else
                            $insertvalues .= '""';
                    } else
                        $insertvalues .= 'NULL';
                    $insertvalues .= ',';
                }
                $salida .= substr($insertvalues, 0, -1) . ");\n";
            }
            $salida .= "\n\n\n";
        }
        return $salida;
    }

    // Restaura la BBDD a partir de las clausulas de la cadena $sql (que se habrán cargado desde algún fichero)
    public function restore($sql)
    {
        $this->db->query('SET FOREIGN_KEY_CHECKS=0');
        $queries = explode(';', $sql);

        foreach ($queries as $q) {
            $q = trim($q);
            if ($q != '')
                $this->db->query($q);
        }
        $this->db->query('SET FOREIGN_KEY_CHECKS=1');
        return true;
    }

    // Borra todas las tuplas de todas las tablas
    public function delete($id)
    {
        $this->db->query('SET FOREIGN_KEY_CHECKS=0');
        // Obtener listado de tablas
        $result = $this->db->query('SHOW TABLES');
        $tablas = $result->fetch_all(MYSQLI_NUM);
        // Borrar cada tabla
        foreach ($tablas as $tab)
            if($tab[0] !== 'users'){
                $this->db->query('DELETE FROM ' . $tab[0]);
            } else {
                $this->db->query('DELETE FROM ' . $tab[0] . ' WHERE id != ' . $id); //No se borra al administrador que decida borrar la base de datos
            }
        $this->db->query('SET FOREIGN_KEY_CHECKS=1');
    }

}