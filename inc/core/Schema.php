<?php
namespace Bookme\Inc\Core;

/**
 * Class Schema
 */
abstract class Schema
{
    /**
     * Drop foreign keys.
     *
     * @param array $tables
     */
    protected function drop_foreign_keys(array $tables )
    {
        /** @var \wpdb $wpdb */
        global $wpdb;

        $query_foreign_keys = sprintf(
            'SELECT table_name, constraint_name FROM information_schema.key_column_usage
                WHERE REFERENCED_TABLE_SCHEMA = SCHEMA() AND REFERENCED_TABLE_NAME IN (%s)',
            implode( ', ', array_fill( 0, count( $tables ), '%s' ) )
        );

        $schema = $wpdb->get_results( $wpdb->prepare( $query_foreign_keys, $tables ) );
        foreach ( $schema as $foreign_key )
        {
            $wpdb->query( "ALTER TABLE `$foreign_key->table_name` DROP FOREIGN KEY `$foreign_key->constraint_name`" );
        }
    }

    /**
     * Drop tables.
     *
     * @param array $tables
     */
    protected function drop( array $tables )
    {
        global $wpdb;

        $this->drop_foreign_keys( $tables );

        $wpdb->query( 'DROP TABLE IF EXISTS `' . implode( '`, `', $tables ) . '` CASCADE;' );
    }

    /**
     * Drop plugin tables.
     */
    protected function drop_plugin_tables()
    {
        $tables = array();

        $plugin_class = Plugin::get_plugin_for( $this );
        foreach ($plugin_class::get_table_classes() as $table_class ) {
            $tables[] = $table_class::get_table_name();
        }

        if ( ! empty ( $tables ) ) {
            $this->drop( $tables );
        }
    }

    /**
     * Drop table columns.
     *
     * @param $table
     * @param array $columns
     */
    protected function drop_table_columns($table, array $columns )
    {
        global $wpdb;

        $get_foreign_keys = sprintf(
            'SELECT constraint_name FROM information_schema.key_column_usage
                WHERE TABLE_SCHEMA = SCHEMA() AND table_name = "%s" AND column_name IN (%s)',
            $table,
            implode( ', ', array_fill( 0, count( $columns ), '%s' ) )
        );
        $constraints = $wpdb->get_results( $wpdb->prepare( $get_foreign_keys, $columns ) );
        foreach ( $constraints as $foreign_key ) {
            $wpdb->query( "ALTER TABLE `$table` DROP FOREIGN KEY `$foreign_key->constraint_name`" );
        }
        foreach ( $columns as $column ) {
            $wpdb->query( "ALTER TABLE `$table` DROP COLUMN `$column`" );
        }
    }

    /**
     * Get list of enum values.
     *
     * @param $table
     * @param $column_name
     * @return string   Like 'value1','value2'
     */
    protected function get_enum_string($table, $column_name )
    {
        global $wpdb;

        $get_enum = $wpdb->prepare(
            'SELECT SUBSTRING(COLUMN_TYPE,5) FROM information_schema.COLUMNS
                WHERE TABLE_NAME = %s AND COLUMN_NAME = %s AND DATA_TYPE = "enum" AND TABLE_SCHEMA = SCHEMA()',
            $table,
            $column_name
        );

        return trim ( $wpdb->get_var( $get_enum ), '()' );
    }

    /**
     * Count of affected rows
     *
     * @return int|null
     */
    public static function get_affected_rows()
    {
        global $wpdb;

        return $wpdb->rows_affected;
    }

}