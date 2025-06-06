<?php if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Class WPN_Helper
 *
 * The WP Ninjas Static Helper Class
 *
 * Provides additional helper functionality to WordPress helper functions.
 */
class WPN_Helper
{

    /**
     * @param $value
     * @return array|string
     */
    public static function addslashes( $value )
    {
        $value = is_array($value) ?
            array_map('WPN_Helper::addslashes' , $value) :
            addslashes($value);
        return $value;
    }

    /**
     * @param $input
     * @return array|string
     */
    public static function utf8_encode( $input ){
        if ( is_array( $input ) )    {
            return array_map( 'WPN_Helper::utf8_encode' , $input );
        } elseif ( function_exists( 'utf8_encode' ) ) {
            return static::iso8859_1_to_utf8( $input );
        } else {
            return $input;
        }
    }

    /**
     * Replace utf8_encode with mimicked functionaliy
     * 
     * Deprecated in PHP8 and removed in PHP9
     * 
     * Replacement credit: https://php.watch/versions/8.2/utf8_encode-utf8_decode-deprecated
     * and https://github.com/symfony/polyfill-php72/blob/v1.26.0/Php72.php#L32-39
     *
     * @param string $string
     * @return string
     */
    public static function iso8859_1_to_utf8( $s) {
        
        if(!is_string($s)){
            return $s;
        }

        $s .= $s;
        $len = \strlen($s);
    
        for ($i = $len >> 1, $j = 0; $i < $len; ++$i, ++$j) {
            switch (true) {
                case $s[$i] < "\x80": $s[$j] = $s[$i]; break;
                case $s[$i] < "\xC0": $s[$j] = "\xC2"; $s[++$j] = $s[$i]; break;
                default: $s[$j] = "\xC3"; $s[++$j] = \chr(\ord($s[$i]) - 64); break;
            }
        }
    
        return substr($s, 0, $j);
    }

    /**
     * @param $input
     * @return array|string
     */
    public static function utf8_decode( $input ){
        if ( is_array( $input ) )    {
            return array_map( 'WPN_Helper::utf8_decode' , $input );
        } elseif ( function_exists( 'utf8_decode' ) ) {
            return self::utf8_to_iso8859_1( $input );
        } else {
            return $input;
        }
    }
    
    /**
     * Replace utf8_decode with mimicked functionaliy
     * 
     * Deprecated in PHP8 and removed in PHP9
     * 
     * Replacement credit: https://php.watch/versions/8.2/utf8_encode-utf8_decode-deprecated
     * and https://github.com/symfony/polyfill-php72/blob/v1.26.0/Php72.php#L40-69
     *
     * @param string $string
     * @return string
     */
    public static function utf8_to_iso8859_1( $string)
    {        
        if(!is_string($string)){
            return $string;
        }

        $s = (string) $string;
        $len = \strlen($s);
    
        for ($i = 0, $j = 0; $i < $len; ++$i, ++$j) {
            switch ($s[$i] & "\xF0") {
                case "\xC0":
                case "\xD0":
                    $c = (\ord($s[$i] & "\x1F") << 6) | \ord($s[++$i] & "\x3F");
                    $s[$j] = $c < 256 ? \chr($c) : '?';
                    break;
    
                case "\xF0":
                    ++$i;
                    // no break
    
                case "\xE0":
                    $s[$j] = '?';
                    $i += 2;
                    break;
    
                default:
                    $s[$j] = $s[$i];
            }
        }
    
        return substr($s, 0, $j);
    }

    /**
     * Function to clean json data before json_decode.
     * @since 3.2
     * @param $input String
     * @return String
     */
    public static function json_cleanup( $input ) {

        /*
         * Remove any unwated (corrupted?) characters from either side of our object.
         */
        $l_trim = strpos( $input, '{' );
        $r_trim = strrpos( $input, '}' ) - $l_trim + 1;
        return substr( $input, $l_trim, $r_trim );
    }

    /**
     * @param $search
     * @param $replace
     * @param $subject
     * @return mixed
     */
    public static function str_replace( $search, $replace, $subject ){
        if( is_array( $subject ) ){
            foreach( $subject as &$oneSubject )
                $oneSubject = WPN_Helper::str_replace($search, $replace, $oneSubject);
            unset($oneSubject);
            return $subject;
        } else {
            return str_replace($search, $replace, $subject);
        }
    }

    /**
     * @param $value
     * @param int $flag
     * @return array|string
     */
    public static function html_entity_decode( $value, $flag = ENT_COMPAT ){
        $value = is_array($value) ?
            array_map( 'WPN_Helper::html_entity_decode' , $value) :
            html_entity_decode( $value, $flag );
        return $value;
    }

    /**
     * @param $value
     * @return array|string
     */
    public static function htmlspecialchars( $value ){
        $value = is_array($value) ?
            array_map( 'WPN_Helper::htmlspecialchars' , $value) :
            htmlspecialchars( $value );
        return $value;
    }

    /**
     * @param $value
     * @return array|string
     */
    public static function stripslashes( $value ){
        $value = is_array($value) ?
            array_map( 'WPN_Helper::stripslashes' , $value) :
            stripslashes($value);
        return $value;
    }

    /**
     * @param $value
     * @return array|string
     */
    public static function esc_html( $value )
    {
        $value = is_array($value) ?
            array_map( 'WPN_Helper::esc_html' , $value) :
            esc_html($value);
        return $value;
    }

    /**
     * @param $value
     * @return array|string
     */
    public static function kses_post( $value )
    {
        $value = is_array( $value ) ?
            array_map(  'WPN_Helper::kses_post' , $value ) :
            wp_kses_post($value);
        return $value;
    }

    /**
     * @param $value
     * @return array|string
     */
    public static function strip_tags( $value )
    {
        $value = is_array( $value ) ?
            array_map( 'WPN_Helper::strip_tags' , $value ) :
            strip_tags( $value );
        return $value;
    }

    /**
     * String to Bytes
     *
     * Converts PHP settings from a string to bytes.
     *
     * @param $size
     * @return float
     */
    public static function string_to_bytes( $size )
    {
        // Remove the non-unit characters from the size.
        $unit = preg_replace('/[^bkmgtpezy]/i', '', $size);

        // Remove the non-numeric characters from the size.
        $size = preg_replace('/[^0-9\.]/', '', $size);

        if ( $unit && is_array( $unit ) ) {
            // Find the position of the unit in the ordered string which is the power of magnitude to multiply a kilobyte by.
            $size *= pow( 1024, stripos( 'bkmgtpezy', $unit[0] ) );
        }

        return round($size);
    }

    public static function str_putcsv( $array, $delimiter = ',', $enclosure = '"', $terminator = "\n" ) {
        // First convert associative array to numeric indexed array
        $workArray = array();
        foreach ($array as $key => $value) {
        $workArray[] = $value;
        }

        $returnString = '';                 # Initialize return string
        $arraySize = count( $workArray );     # Get size of array

        for ( $i=0; $i<$arraySize; $i++ ) {
            // Nested array, process nest item
            if ( is_array( $workArray[$i] ) ) {
                $returnString .= self::str_putcsv( $workArray[$i], $delimiter, $enclosure, $terminator );
            } else {
                switch ( gettype( $workArray[$i] ) ) {
                    // Manually set some strings
                    case "NULL":     $_spFormat = ''; break;
                    case "boolean":  $_spFormat = ($workArray[$i] == true) ? 'true': 'false'; break;
                    // Make sure sprintf has a good datatype to work with
                    case "integer":  $_spFormat = '%d'; break;
                    case "double":   $_spFormat = '%0.2f'; break;
                    case "string":   $_spFormat = '%s'; $workArray[$i] = str_replace("$enclosure", "$enclosure$enclosure", $workArray[$i]); break;
                    // Unknown or invalid items for a csv - note: the datatype of array is already handled above, assuming the data is nested
                    case "object":
                    case "resource":
                    default:         $_spFormat = ''; break;
                }
                $returnString .= sprintf('%2$s'.$_spFormat.'%2$s', $workArray[$i], $enclosure);
                $returnString .= ($i < ($arraySize-1)) ? $delimiter : $terminator;
            }
        }
        // Done the workload, return the output information
        return $returnString;
    }

    public static function get_query_string( $key, $default = FALSE )
    {
        if( ! isset( $_GET[ $key ] ) ) return $default;

        $value = self::htmlspecialchars( $_GET[ $key ] );

        if( is_array( $value ) ) $value = reset( $value );

        return $value;
    }

    public static function sanitize_text_field( $data )
    {
        if( is_array( $data ) ){
            return array_map( 'WPN_Helper::sanitize_text_field' , $data );
        }
        return sanitize_text_field( $data );
    }

    public static function get_plugin_version( $plugin )
    {
        $plugins = get_plugins();

        if( ! isset( $plugins[ $plugin ] ) ) return false;

        return $plugins[ $plugin ][ 'Version' ];
    }

    public static function is_func_disabled( $function )
    {
        if( ! function_exists( $function ) ) return true;
        $disabled = explode( ',',  ini_get( 'disable_functions' ) );
        return in_array( $function, $disabled );
    }

    public static function maybe_unserialize( $original )
    {
        // Repalcement for https://codex.wordpress.org/Function_Reference/maybe_unserialize
        if ( is_serialized( $original ) ){
            // Ported with php5.2 support from https://magp.ie/2014/08/13/php-unserialize-string-after-non-utf8-characters-stripped-out/
            $parsed = preg_replace_callback( '!s:(\d+):"(.*?)";!s', 'WPN_Helper::parse_utf8_serialized' , $original );
            $parsed = @unserialize( $parsed );

            return ( $parsed ) ? $parsed : unserialize( $original ); // Fallback if parse error.
        }
        return $original;
    }
    
    /**
     * Function to fetch our cache from the upgrades table (if it exists there).
     * 
     * @param $id (int) The form ID.
     * 
     * @since 3.3.7
     */
    public static function get_nf_cache( $id ) {
        // See if we have the data in our table already.
        global $wpdb;
        $sql = "SELECT cache FROM `{$wpdb->prefix}nf3_upgrades` WHERE id = " . intval( $id );
        $result = $wpdb->get_results( $sql, 'ARRAY_A' );
        // If so...
        if ( ! empty( $result ) ) {
            // Unserialize the result.
            $value = WPN_Helper::maybe_unserialize( $result[ 0 ][ 'cache' ] );
            // Return it.
            return $value;
        } // Otherwise... (We don't have the data.)
        else {
            // Get it from the options table.
            return get_option( 'nf_form_' . $id );
        }
    }
    
    /**
     * Function to insert or update our cache in the upgrades table (if it exists).
     * 
     * @param $id (int) The form ID.
     * @param $data (string) The form cache.
     * @param $stage (int) The target stage of this update. Default to the current max stage.
     * 
     * @since 3.3.7
     * @updated 3.4.0
     */
    public static function update_nf_cache( $id, $data, $stage = 0 ) {
        $stage = ( $stage ) ? $stage : WPN_Helper::get_stage();
        // Serialize our data.
        $cache = serialize( $data );
        global $wpdb;
        // See if we've already got a record.
        $sql = "SELECT id FROM `{$wpdb->prefix}nf3_upgrades` WHERE id = " . intval( $id );
        $result = $wpdb->get_results( $sql, 'ARRAY_A' );
        // If we don't already have the data...
        if ( empty( $result ) ) {
            // Insert it.
            $sql = $wpdb->prepare( "INSERT INTO `{$wpdb->prefix}nf3_upgrades` (id, cache, stage) VALUES (%d, %s, %s)", intval( $id ), $cache, intval( $stage ) );
        } // Otherwise... (We do have the data.)
        else {
            // Update the existing record.
            $sql = $wpdb->prepare( "UPDATE `{$wpdb->prefix}nf3_upgrades` SET cache = %s, stage = %d WHERE id = %d", $cache, intval( $stage ), intval( $id ) );
        }
        $wpdb->query( $sql );
    }

    /**
     * Function to retrieve our upgrade stage.
     * Remove this after the cache has been resolved.
     * 
     * @return int
     * 
     * @since 3.4.0
     */
    public static function get_stage() {
        $ver = static::getNfDbVersion();
        $stack = explode( '.', $ver );
        return intval( array_pop( $stack ) );
    }
        
    /**
     * Provide the current Ninja Forms database version.
     *
     * @return string
     */
    protected static function getNfDbVersion(  ):string {
        $ver = Ninja_Forms::$db_version;
        return $ver;
    }

    /**
     * Function to build our form cache from the table.
     * 
     * @param $id (int) The form ID.
     * @since 3.3.18
     * @return  $form_cache Array of form data.
     * @updated 3.4.0
     */
    public static function build_nf_cache( $id ) {
        $form = Ninja_Forms()->form( $id )->get();

        $form_cache = array(
            'id'        => $id,
            'fields'    => array(),
            'actions'   => array(),
            'settings'  => $form->get_settings(),
        );
        
        $fields = Ninja_Forms()->form( $id )->get_fields();
        
        foreach( $fields as $field ){
            // If the field is set.
            if ( ! is_null( $field ) && ! empty( $field ) ) {
                array_push( $form_cache[ 'fields' ], array( 'settings' => $field->get_settings(), 'id' => $field->get_id() ) );
            }
        }

        $actions = Ninja_Forms()->form( $id )->get_actions();

        foreach( $actions as $action ){
            // If the action is set.
            if ( ! is_null( $action ) && ! empty( $action ) ) {
                array_push( $form_cache[ 'actions' ], array( 'settings' => $action->get_settings(), 'id' => $action->get_id() ) );
            }
        }
        
        static::update_nf_cache( $id, $form_cache );

        return $form_cache;
    }
    
    /**
     * Function to delete our cache.
     * 
     * @param $id (int) The form ID.
     * 
     * @since 3.3.7
     */
    public static function delete_nf_cache( $id ) {
        global $wpdb;
        $sql = "DELETE FROM `{$wpdb->prefix}nf3_upgrades` WHERE id = " . intval( $id );
        $wpdb->query( $sql );
        delete_option( 'nf_form_' . intval( $id ) );
    }

    private static function parse_utf8_serialized( $matches )
    {
        if ( isset( $matches[2] ) ){
            return 's:'.strlen($matches[2]).':"'.$matches[2].'";';
        }
    }

    /**
     * This funtion gets/creates the Ninja Forms gate keeper( a random integer 
     * between 1 and 100 ). We will use this number when deciding whether a
     * particular install is eligible for an upgrade or whatever else we decide
     * to use it for
     * 
     * @return int $zuul
     * 
     * @since 3.4.0
     */
    public static function get_zuul() {
        $zuul = get_option( 'ninja_forms_zuul', -1 );

        if( -1 === $zuul ) {
            $zuul = rand( 1, 100 );
            update_option( 'ninja_forms_zuul', $zuul, false );
        }

        return $zuul;
    }

    /**
     * This function will return true/false based on an option( ninja_forms_zuul )
     * and a threshold that we set. We can use this to limit updates
     * 
     * @param $threshold
     * 
     * @return bool
     * 
     * @since 3.4.0
     */
    public static function gated_release( $threshold = 0 ) {
        $gatekeeper = $threshold >= self::get_zuul();
        $gatekeeper = apply_filters( 'ninja_forms_gatekeeper', $gatekeeper );
        
        return $gatekeeper;
    }

    /**
     * Is Maintenance
     *
     * Checks the upgrades table to see if the form the user is viewing
     * is under maintenance mode.
     *
     * @since 3.4.0
     *
     * @param $form_id - The ID of the form we are checking.
     *
     * @return boolean
     */
    public static function form_in_maintenance( $form_id ) {
        global $wpdb;

        $db_version = get_option( 'ninja_forms_db_version' );

        if( ! $db_version ) return false;

        // Exit early if the column doesn't exist.
        if( version_compare( '1.3', $db_version, '>' ) ) return false;

        // Get our maintenance value from the DB and return it at the zero position.
        $maintenance = $wpdb->get_row(
            "SELECT `maintenance` FROM `{$wpdb->prefix}nf3_upgrades` WHERE `id` = {$form_id}", 'ARRAY_A'
        );

        /*
         *  If maintenance isn't empty and basic on maintenance's value
         *  return a boolean value.
         */
        if( ! empty( $maintenance ) && 1 == $maintenance[ 'maintenance' ] ) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * This function either put all forms in maintenance mode or remove maintenance
     * mode for all forms. Depending on the input parameters
     * 
     * @since 3.4.0
     * 
     * @param $mode - Default 0 ( Take all forms out of maintenance mode )
     */
    public static function set_forms_maintenance_mode( $mode = 0 ) {
        global $wpdb;

        // default is 0, so if we get passed bad data, just use 0
        if( ! in_array( $mode, array( 0, 1 ) ) ) {
            $mode = 0;
        }

        // set maintenance flag to $mode (0 or 1)
        $sql = $wpdb->prepare( "UPDATE `{$wpdb->prefix}nf3_upgrades` SET "
            . "maintenance = %d", intval( $mode ) );
        
        $wpdb->query( $sql );
    }

    /**
     * We'll use to determine if we need to use the form cache or not. This will
     * be used for all users not on the newest version of the database
     * 
     * @return boolean
     */
    public static function use_cache() {
        return true;

        $cache_mode = intval( get_option('ninja_forms_cache_mode') );
        // if we've already decided to use the cache return true and exit.
        if( 0 < $cache_mode ) return true;

        $db_version = get_option('ninja_forms_db_version');
        // If not in cache mode, get the db version and return true if we aren't at a certain threshold version-wise
        if( ! $db_version || version_compare($db_version, '1.4', '<' )) {
            return true;
        }

        $finished_updates = get_option( 'ninja_forms_required_updates', false );
        // make sure we've run the lastest update to reconcile db with cache field values
        if( $finished_updates && !isset( $finished_updates[ 'CacheFieldReconcilliation' ] ) ) {
            return true;
        }

        return false;
    }

    /**
     * Sanitizes single/multiple CSS classNames
     *
     * Explodes on space, sanitize each className, implode with space to recombine
     * @param string $value
     * @return string
    */
    public static function sanitize_classes($value):string {
        
        $outgoing = $value;
        $sanitized = [];
        
        $exploded = explode(' ',$value);

        foreach($exploded as $singleClass){
            $sanitized[] = sanitize_html_class($singleClass);
        }

        $outgoing = implode(' ',$sanitized);

        return $outgoing;
    }

    /**
     * Sanitizes string values for field settings 
     * 
     * WIP methods can still be implemented for this.
     *
     * @param string $key Setting name
     * @param string $value of setting
     * @return string sanitized value for setting
    */
    public static function sanitize_string_setting_value($key, $value):string {

        if( in_array( $key, ["element_class", "container_class"] ) ) {
            $value = self::sanitize_classes($value);
        } else if( in_array( $key, ["label"] )){
            $value = self::sanitize_text_field($value);
        }


        return $value;
    }

    /**
     * Check the DISALLOW_UNFILTERED_HTML constant value and return early if true. 
     * If false, return opposite for 'unfiltered_html' current user capability
     * 
     * @return bool
    */
    public static function maybe_disallow_unfiltered_html_for_sanitization():bool {

        /**
         * Exit early if the config setting is TRUE to mimic WordPress capability check.
         */
        if( defined( 'DISALLOW_UNFILTERED_HTML' ) && DISALLOW_UNFILTERED_HTML ) return true;

        $disallow_unfiltered_html = ! current_user_can( 'unfiltered_html' );
        return $disallow_unfiltered_html;
    }

    /**
     * Check the DISALLOW_UNFILTERED_HTML constant value only on the escaping side
     * 
     * @return bool
    */
    public static function maybe_disallow_unfiltered_html_for_escaping():bool {

        // Default intentinally left set to false to avoid breaking countless pre-existing forms using this feature.
        $disallow_unfiltered_html = defined( 'DISALLOW_UNFILTERED_HTML' ) ? DISALLOW_UNFILTERED_HTML : false;

        return $disallow_unfiltered_html;
    }

    /**
     * Sanitize output to csv to prevent formula injection.
     * 
     * @param String $value The value to be escaped.
     * @return String
     */
    public static function maybe_escape_csv_column( $value ):string {
        if (!is_string($value) && !is_numeric($value)) {
            if(is_array($value)){
                $value = implode(' ', $value);
            }else{
                throw new Exception('Incoming value to maybe_escape_csv_column is neither string nor array');
            }
        }

        if( 0 < strlen($value ) ) {
            $first_char = substr( $value, 0, 1 );
            if( in_array( $first_char, array( '=', '@', '+', '-' ) ) ) {
                $value = "'" . $value;
            }
        }
        return $value;
    }
    
} // End Class WPN_Helper
