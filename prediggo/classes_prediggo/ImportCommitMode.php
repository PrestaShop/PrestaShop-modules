<?php


/**
 * Constants representing available recommendation methods.
 *
 * @package prediggo4php
 * @subpackage constants
 *
 * @author Stef
 */
class ImportCommitMode
{

    /**
    * Changes are updated in memory only
    */
    const COMMIT_IN_MEMORY = "COMMIT_IN_RAM_ONLY";

    /**
    * Changes are updated in memory and persisted in DB
    */
    const COMMIT_IN_DB = "COMMIT_IN_RAM_ONLY";

    /**
    * Changes are updated in DB, and a complete import process is triggered
    */
    const COMMIT_IN_DB_AND_RELOAD = "COMMIT_IN_RAM_AND_DB_AND_PERFORM_DATA_RELOAD";

}

