<?php
namespace Evolvo\LaravelCodeGenerators\MigrationParsers;

use Evolvo\LaravelCodeGenerators\Converters\LaravelConverter;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class MigrationParser
{
    private array $updatedTables;

    public function __construct()
    {
        $this->updatedTables = [];
        $batch = DB::table('migrations')->max('batch');
        $newMigrations = DB::table('migrations')->where('batch', $batch)->get();
        foreach ($newMigrations as $newMigration) {
            $migrtionFilePath = base_path() . '/database/migrations/' . $newMigration->migration . '.php';
            if(!File::exists($migrtionFilePath)) continue;
            $migrationFileContents = File::get($migrtionFilePath);

            //todo: refactor to get all tables in up method in single regex
            $upMethodMatches = [];
            preg_match("/(up\(\)).+?(?=\}\n)/s",$migrationFileContents, $upMethodMatches);
            if (!array_key_exists(0, $upMethodMatches)) continue;
            $upMethodContents = $upMethodMatches[0];
            preg_match_all("/(Schema::table).+?(?=}\);\n)/s", $upMethodContents, $tables);
            foreach ($tables[0] as $tableUpdates) {

                //todo: refactor to simplify - extracting table name with something like this /Schema::table\(['"](.*?)['"],/g to get rit of
                //also extract multilple tables

                $singleQuotes = true;
                preg_match("/(Schema::table\(').+?(?=')/" , $tableUpdates, $tableLine);
                if (!array_key_exists(0, $tableLine))
                {
                    preg_match("/(Schema::table\(\").+?(?=\")/" , $tableUpdates, $tableLine);
                    $singleQuotes = false;
                }
                $tableLine = $tableLine[0];
                if ($singleQuotes) $table = explode("'", $tableLine)[1];
                else $table = explode('"', $tableLine)[1];

                //todo: change regex to match single and double quotes in single check
                //todo: refactor - change regex not to match table->dropColumn, table->index and other not related methods
                // refer to Illuminate\Database\Schema\Blueprint to whitelist methods that shouldbe matched, renames included
                $singleQuotes2 = false;
                preg_match_all("/(table->).+?(?=\"\))/", $tableUpdates, $tableAttributesUnparsed);
                if ($tableAttributesUnparsed[0] == [])
                {
                    preg_match_all("/(table->).+?(?=\'\))/", $tableUpdates, $tableAttributesUnparsed);
                    $singleQuotes2 = true;
                }

                $tableAttributes = [];
                foreach ($tableAttributesUnparsed[0] as $tableAttributeUnparsed)
                {
                    if ($singleQuotes2) $tableAttributeName = explode("'", $tableAttributeUnparsed)[1];
                    else $tableAttributeName = explode('"', $tableAttributeUnparsed)[1];
                    $tableAttributes[] = $tableAttributeName;
                }
                //end refactor section



                $tableAttributes = collect(DB::select('show columns from ' . $table))
                    ->filter(function($column) use ($tableAttributes){
                        return in_array($column->Field,$tableAttributes);
                    });

                $updatedTable = array_key_exists($table, $this->updatedTables) ?
                    $this->updatedTables[$table] : new UpdatedTable($table);
                foreach($tableAttributes as $tableAttribute)
                    $updatedTable->addColumn($tableAttribute);
                $this->updatedTables[$table] = $updatedTable;
            }
        }
    }

    /**
     * Returns a list of Personality objects
     * @return UpdatedTable[]
     */
    public function updatedTables(){
        return $this->updatedTables;
    }
}
