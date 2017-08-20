<?php
/*
 * General Discoin stuff.
 * 
 * @author MacDue
 */
namespace Discoin;

/*
 * Base object for other Discoin objects
 * 
 * @author MacDue
 */
abstract class Object implements \MongoDB\BSON\Persistable
{
  
    // Temp to update to BSON
    public static function convert($std_obj)
    {
        if ($std_obj instanceof Object) {
            var_dump($std_obj);
            return;
        }
        foreach ($std_obj as $attr_name => $value)
            if (is_object($value))
                $std_obj->$attr_name = (array) $value;
        $temp = serialize($std_obj);
        $class_name = get_called_class();
        // This is a great hack!
        $temp = preg_replace("@^O:8:\"stdClass\":@","O:".strlen($class_name).":\"$class_name\":",$temp);
        $object = unserialize($temp);
        $object->save();
        echo "DoneThing-";
    }

    function bsonSerialize() {
        return get_object_vars($this);
    }

    function bsonUnserialize(array $data) {
        unset($data["_id"], $data["__pclass"]);
        foreach ($data as $name => $value) {
            // Fix arrays (that are set to objects for some reason)
            if (is_object($value)) $value = (array) $value;
            $this->{$name} = $value;
        }
    }
    
    abstract public function save();
    
}


function is_owner($user)
{
    $config = get_config();
    
    foreach ($config->owners as $owner)
        if ($owner->user === $user)
            return True;
    return False;
}


function get_config()
{
    return json_decode(file_get_contents(__DIR__.'/config.json'));
}


function load_config()
{
    $config = get_config();

    // General
    define("TRANSACTION_LIMIT_RESET", $config->transactionLimitReset);
    define("TRANSACTION_WEBHOOK", $config->discord->transactionWebhook);
    
    // Discoin
    $discord_config = $config->discord;
    define("CLIENT_ID", $discord_config->clientId);
    define("CLIENT_SECRET", $discord_config->clientSecret);
    define("PROTCOL", $config->protcol);
    
    // Mongo
    $mongo_config = \Discoin\get_config()->mongo;
    define("MONGO_USER", $mongo_config->user);
    define("MONGO_PASS", $mongo_config->password);
    define("MONGO_HOST", $mongo_config->host);
    define("DATABASE", $mongo_config->database);
    
    define("CONFIG_LOADED", True);
}


if (!defined("CONFIG_LOADED"))
    load_config();

?>
