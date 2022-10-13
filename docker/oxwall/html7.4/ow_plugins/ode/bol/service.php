<?php

class ODE_BOL_Service
{
    const ENTITY_TYPE = 'datalet_entity';

    /**
     * Singleton instance.
     *
     * @var ODE_BOL_Service
     */
    private static $classInstance;

    /**
     * Returns an instance of class (singleton pattern implementation).
     *
     * @return ODE_BOL_Service
     */
    public static function getInstance()
    {
        if ( self::$classInstance === null )
        {
            self::$classInstance = new self();
        }

        return self::$classInstance;
    }

    private function __construct()
    {
    }

    public function getAll()
    {
        return ODE_BOL_DataletDao::getInstance()->findAll();
    }

    public function getDataletById($id)
    {
        $example = new OW_Example();
        $example->andFieldEqual('id', $id);
        $result = ODE_BOL_DataletDao::getInstance()->findObjectByExample($example);
        return $result;
    }

    public function getDataletByPostId($id, $plugin="")
    {
        $dbo = OW::getDbo();

        //TODO FIX TABLE PREFIX NAME
        $query = "SELECT *
                  FROM ow_ode_datalet JOIN ow_ode_datalet_post ON ow_ode_datalet.id = ow_ode_datalet_post.dataletId
                  WHERE ow_ode_datalet_post.postId = " . $id . " AND
                  ow_ode_datalet_post.plugin = '". $plugin ."';";

        return $dbo->queryForRow($query);
    }

    public function getDataletByPostIdWhereArray($id, array $plugin)
    {
        $dbo = OW::getDbo();

        //TODO FIX TABLE PREFIX NAME
        $query = "SELECT *
                  FROM ow_ode_datalet JOIN ow_ode_datalet_post ON ow_ode_datalet.id = ow_ode_datalet_post.dataletId
                  WHERE ow_ode_datalet_post.postId = " . $id . " AND
                  ow_ode_datalet_post.plugin IN ('". implode("','", $plugin) ."');";

        return $dbo->queryForRow($query);
    }

    public function getDataletsById($id, $plugin)
    {
        $query = "SELECT ow_ode_datalet.id FROM ow_ode_datalet JOIN ow_ode_datalet_post ON ow_ode_datalet.id = dataletId ";

        switch($plugin)
        {
            case "newsfeed" :

                $commentEntityId = " SELECT id FROM ow_base_comment_entity WHERE entityId = ".$id." AND pluginKey = 'newsfeed' ";
                $commentsId = " SELECT id FROM ow_base_comment WHERE commentEntityId = (".$commentEntityId.") ";
                $query .= "WHERE postId = ".$id." AND plugin = 'newsfeed' OR postId IN (".$commentsId.") AND plugin = 'comment'";
                break;

            case "event" :

                $commentEntityId = " SELECT id FROM ow_base_comment_entity WHERE entityId = ".$id." AND pluginKey = 'event' ";
                $commentsId = " SELECT id FROM ow_base_comment WHERE commentEntityId = (".$commentEntityId.") ";
                $query .= "WHERE postId = ".$id." AND plugin = 'event' OR postId IN ($commentsId) AND plugin = 'comment'";
                break;

            case "comment" :

                $query .= "WHERE postId = ".$id." AND plugin = 'comment'";
                break;

            case "topic" :

                $forumsId = " SELECT id FROM ow_forum_post WHERE topicId = ".$id." ";
                $query .= "WHERE postId IN ($forumsId) AND plugin = 'forum'";
                break;

            case "forum" :

                $query .= "WHERE postId = ".$id." AND plugin = 'forum'";
                break;

        }

        //comment dataletId < newsfeed/event dataletId
        $query .= " ORDER BY dataletId DESC;";

        $dbo = OW::getDbo();
        return $dbo->queryForList($query);
    }

    public function privateRoomDatalet($component, $fields, $userId, $params, $data='', $dataletId='')
    {
        ODE_CLASS_Helper::sanitizeDataletInput($component, $params, $fields);

        if(empty($dataletId))
        {
            $dt = new ODE_BOL_Datalet();
        }
        else
        {
            $example = new OW_Example();
            $example->andFieldEqual('id', $dataletId);
            $example->andFieldEqual('ownerId', $userId);
            $dt = ODE_BOL_DataletDao::getInstance()->findObjectByExample($example);
        }

        $dt->component = $component;
        $dt->fields    = $fields;
        $dt->ownerId   = $userId;
        $dt->params    = $params;
        $dt->status    = 'approved';
        $dt->privacy   = 'everybody';
        $dt->data      = $data;
        ODE_BOL_DataletDao::getInstance()->save($dt);

        $this->createDataletImage($dt->id);

        return $dt->id;
    }

    public function getDataletInfo($id_post, $id_datalet)
    {
        $dbo = OW::getDbo();

        if(!empty($id_datalet))
            $sql = "select * from " . OW_DB_PREFIX . "ode_datalet where id = " . $id_datalet;
        else
            $sql = "select * from " . OW_DB_PREFIX . "ode_datalet where id = (select dataletId from ow_ode_datalet_post where postId = " . $id_post . ")";

        return $dbo->queryForRow($sql);
    }

    public function getPostInfo($postId, $isPublicRoom)
    {
        $dbo = OW::getDbo();

        if($isPublicRoom == "true")
            $sql = "select * from " . OW_DB_PREFIX . "base_comment where id = " . $postId;
        else
            $sql = "select * from " . OW_DB_PREFIX . "newsfeed_action where entityId = " . $postId;

        return $dbo->queryForRow($sql);
    }

    /*FOR COCREATION ROOM*/

    public function saveDatalet($datalet, $fields, $ownerId, $params, $cache=""){
        ODE_CLASS_Helper::sanitizeDataletInput($datalet, $dataset, $fields);

        $dt            = new ODE_BOL_Datalet();
        $dt->component = $datalet;
        $dt->fields    = $fields;
        $dt->ownerId   = $ownerId;
        $dt->params    = $params;
        $dt->status    = 'approved';
        $dt->privacy   = 'everybody';
        $dt->data      = $cache;
        ODE_BOL_DataletDao::getInstance()->save($dt);

        return $dt;
    }
    /*END COCREATION ROOM*/


    public function addDatalet($datalet, $fields, $ownerId, $params, $postId, $plugin, $cache="")
    {
        ODE_CLASS_Helper::sanitizeDataletInput($datalet, $dataset, $fields);

        $dt            = new ODE_BOL_Datalet();
        $dt->component = $datalet;
        $dt->fields    = $fields;
        $dt->ownerId   = $ownerId;
        $dt->params    = $params;
        $dt->status    = 'approved';
        $dt->privacy   = 'everybody';
        $dt->data      = $cache;
        ODE_BOL_DataletDao::getInstance()->save($dt);

        $dtp            = new ODE_BOL_DataletPost();
        $dtp->postId    = $postId;
        $dtp->dataletId = $dt->id;
        $dtp->plugin    = $plugin;
        ODE_BOL_DataletPostDao::getInstance()->save($dtp);

        $this->createDataletImage($dt->id);

        return $dt->id;
    }

    public function createDataletImage($dataletId)
    {
        $class_dir = OW::getPluginManager()->getPlugin('ode')->getRootDir() . 'lib';
        chdir($class_dir);

        $command = "nohup node image_generator.js {$dataletId} > /dev/null 2>/dev/null &";
        shell_exec($command);
    }

    public function deleteDataletsById($id, $plugin)
    {
        $datalets = $this->getDataletsById($id, $plugin);

        foreach($datalets as &$dt)
        {
            //echo('--'.$dt['id'].'--');
            ODE_BOL_DataletDao::getInstance()->deleteById($dt['id']);

            $ex = new OW_Example();
            $ex->andFieldEqual('dataletId', $dt['id']);
            ODE_BOL_DataletPostDao::getInstance()->deleteByExample($ex);

            $this->deleteDataletImage($dt['id']);
        }
    }

    public function deleteDataletAndAssociationByDataletAndCommentId($dataletId, $commentId, $plugin)
    {
        $dbo = OW::getDbo();

        //Delete Datalet
        $sql = "DELETE FROM ow_ode_datalet_post WHERE postId = {$commentId} AND dataletId = {$dataletId} AND plugin = '{$plugin}'; ";
        $dbo->query($sql);
        //Delete Datalet
        $sql = "DELETE FROM ow_ode_datalet WHERE id = {$dataletId}; ";
        $dbo->query($sql);

        $this->deleteDataletImage($dataletId);
    }

    public function deleteDataletByDataletId($dataletId)
    {
        $e = new OW_Example();
        $e->andFieldEqual('id', $dataletId);
        ODE_BOL_DataletDao::getInstance()->deleteByExample($e);

        $this->deleteDataletImage($dataletId);
    }

    private function deleteDataletImage($dataletId)
    {
        $class_dir = OW::getPluginManager()->getPlugin('ode')->getRootDir() . 'datalet_images';
        chdir($class_dir);
        $command = "rm  datalet_{$dataletId}.png";
        shell_exec($command);
    }

    public function getSettingByKey($key)
    {
        $example = new OW_Example();
        $example->andFieldEqual('key', $key);
        $pref = ODE_BOL_SettingDao::getInstance()->findObjectByExample($example);
        return $pref;
    }

    public function saveSetting($key, $value)
    {
        $pref = $this->getSettingByKey($key);

        if($pref)
        {
            $pref->value = $value;
        }
        else
        {
            $pref = new ODE_BOL_Settings();
            $pref->key = $key;
            $pref->value = $value;
        }

        ODE_BOL_SettingDao::getInstance()->save($pref);

    }

    public function deleteSettings($key)
    {
        $pref = $this->getSettingByKey($key);
        ODE_BOL_SettingDao::getInstance()->delete($pref);
    }

    public function checkIfAdmin($id){
        /*$admins  =  $this->getAdminList();
        foreach($admins as $admin)
        {
            if($admin->userId == $id){
                return true;
            }
        }
        return false;*/
        return true;
    }

    /**** PROVIDERS ****/

    public function getProviderList()
    {
        return ODE_BOL_ProviderDao::getInstance()->findAll();
    }

    public function addProvider($name, $url)
    {
        $provider = new ODE_BOL_Provider();
        $provider->name = trim($name);
        $provider->url = trim($url);
        ODE_BOL_ProviderDao::getInstance()->save($provider);
    }

    public function deleteProvider($id)
    {
        ODE_BOL_ProviderDao::getInstance()->deleteById($id);
    }

}
