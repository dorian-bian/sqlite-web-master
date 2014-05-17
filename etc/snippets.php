<?php
$_SNIPPETS = array(
    array(
        'title' => 'Select',
        'value' => array(
            array(
                'title' => 'SELECT * FROM ...',
                'value' => 'SELECT * FROM ...'
            ),
            array(
                'title' => 'SELECT * FROM ... WHERE ...',
                'value' => 'SELECT * FROM ... WHERE ...'
            )
        )
    ),
    array(
        'title' => 'Data Manipulation',
        'value' => array(
            array(
                'title' => 'DELETE FROM ... WHERE ...',
                'value' => 'DELETE FROM ... WHERE ...'
            ),
            array(
                'title' => 'INSERT INTO ... (...) VALUES(...)',
                'value' => 'INSERT INTO ... (...) VALUES(...)'
            ),
            array(
                'title' => 'INSERT INTO ... (...) SELECT * FROM ...',
                'value' => 'INSERT INTO ... (...) SELECT * FROM ...'
            ),
            array(
                'title' => 'UPDATE ... SET ... WHERE ...',
                'value' => 'UPDATE ... SET ... WHERE ...'
            ),
            array(
                'title' => 'REPLACE INTO ... (...) VALUES(...)',
                'value' => 'REPLACE INTO ... (...) VALUES(...)'
            ),
            array(
                'title' => 'REPLACE INTO ... (...) SELECT * FROM ...',
                'value' => 'REPLACE INTO ... (...) SELECT * FROM ...'
            ),
        )
    ),
    array(
        'title' => 'Schema',
        'value' => array(
            array(
                'title' => 'CREATE TABLE ... (...)',
                'value' => 'CREATE TABLE ... (...)'
            ),
            array(
                'title' => 'CREATE TABLE ... AS ...',
                'value' => 'CREATE TABLE ... AS ...'
            ),
            array(
                'title' => 'ALTER TABLE ... RENAME TO ...',
                'value' => 'ALTER TABLE ... RENAME TO ...'
            ),
            array(
                'title' => 'ALTER TABLE ... ADD COLUMN ...',
                'value' => 'ALTER TABLE ... ADD COLUMN ...'
            ),
            array(
                'title' => 'CREATE INDEX ... ON ... (...)',
                'value' => 'CREATE INDEX ... ON ... (...)'
            ),
            array(
                'title' => 'CREATE VIEW ... AS ...',
                'value' => 'CREATE VIEW ... AS ...'
            ),
            array(
                'title' => 'CREATE TRIGGER ...',
                'value' => 'CREATE TRIGGER ... BEFORE|AFTER DELETE|INSERT|UPDATE ON ...  BEGIN ...; END'
            )
        )
    ),
    array(
        'title' => 'Drop',
        'value' => array(
            array(
                'title' => 'DROP TABLE ...',
                'value' => 'DROP TABLE ...'
            ),
            array(
                'title' => 'DROP INDEX ...',
                'value' => 'DROP INDEX ...'
            ),
            array(
                'title' => 'DROP VIEW ...',
                'value' => 'DROP VIEW ...'
            ),
            array(
                'title' => 'DROP TRIGGER ...',
                'value' => 'DROP TRIGGER ...'
            )
        )
    ),
    array(
        'title' => 'Misc',
        'value' => array(
            array(
                'title' => 'PRAGMA table_info (...)',
                'value' => 'PRAGMA table_info (...)'
            ),
            array(
                'title' => 'PRAGMA index_list (...)',
                'value' => 'PRAGMA index_list (...)'
            ),
            array(
                'title' => 'PRAGMA index_info (...)',
                'value' => 'PRAGMA index_info (...)'
            ),
            array(
                'title' => 'PRAGMA foreign_key_list (...)',
                'value' => 'PRAGMA foreign_key_list (...)'
            ),
            array(
                'title' => 'PRAGMA compile_options',
                'value' => 'PRAGMA compile_options'
            ),
            array(
                'title' => 'PRAGMA collation_list',
                'value' => 'PRAGMA collation_list'
            ),
            array(
                'title' => 'REINDEX ...',
                'value' => 'REINDEX ...'
            ),
            array(
                'title' => 'EXPLAIN QUERY PLAN ...',
                'value' => 'EXPLAIN QUERY PLAN ...'
            )
        )
    ),

);
?>
