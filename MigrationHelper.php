<?php
   header('Content-Type: text/plain; charset=utf-8');


   $db = "magellano"; // your db name

   // simple db connection
    class DB {
        public static function connect(){

            $connect = new mysqli("YOUR_HOST", "YOUR_USERNAME", "YOUR_PASSWORD", "YOUR_DB");
            return $connect;
        }
    }


    $connector=DB::connect();
    // query to select all tables of your database
    $queryTables="SELECT TABLE_NAME FROM information_schema.TABLES WHERE TABLE_SCHEMA = '$db'";
    $resultsTb=$connector->query($queryTables);
    if ($connector->error) {
        echo $connector->error;
    } else {

        // loop through each table of database
        while ($rowTb=$resultsTb->fetch_assoc()) {

            $tableName=$rowTb['TABLE_NAME'];
            $tableNameLower=strtolower($rowTb['TABLE_NAME']);
            $className=ucfirst(strtolower($rowTb['TABLE_NAME']));
            $columns=array();
            $output="<?php \n\n";

            /*
             * start creating Migration for current table
             *
             *
             */
            $output.="use Illuminate\Database\Schema\Blueprint;"."\n";
            $output.="use Illuminate\Database\Migrations\Migration;"."\n\n";

            $output.="class Create".$className." extends Migration {"."\n\n";
            $output.="/**\n* Run the migrations.\n*\n* @return void\n*/\n\n";
            $output.="public function up()"."\n";
            $output.="{"."\n";
            $output.="Schema::create('".$tableNameLower."', function(Blueprint ".'$table'.")"."\n";
            $output.="{"."\n";
            /*
             * query the table for columns information to fill in the Schema
             *
             */
            $query="SELECT COLUMN_NAME, DATA_TYPE FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA='$db' AND TABLE_NAME='$tableName'";
            $results=$connector->query($query);
            if ($connector->error) {
                echo $connector->error;
            } else {
                    /*
                     *
                     * loop through columns
                     *
                     */
                while ($row=$results->fetch_assoc()) {


                    $columnName=$row['COLUMN_NAME'];
                    $columnType=$row['DATA_TYPE'];


                    //check just some DATA TYPE
                    //PLEASE COMPLETE THIS switch with more types
                    switch($row['DATA_TYPE']) {
                        case "int":
                            $assignType='integer';
                            break;
                        case "double":
                            $assignType='double';
                            break;
                        case "float":
                            $assignType='double';
                            break;
                        case "date":
                            $assignType='date';
                            break;
                        case "timestamp":
                            $assignType='timestamp';
                            break;
                        default:
                            $assignType='string';
                    }

                    /*
                     *
                     * create migration details for each column
                     *
                     */
                    if ($columnName=='id') {
                        $output.='$table->'."increments('id');"."\n";
                    } elseif ($columnName=='password') {
                        $output.='$table->'."string('password', 60);"."\n";
                    } else {
                        $output.='$table->'.$assignType."('".$columnName."');"."\n";
                    }
                    /*
                     * end loop
                     *
                     */

                }


                /*
                 * add rememberToken and timestamps
                 */

                $output.='$table->rememberToken();'."\n";
                $output.='$table->timestamps();'."\n";

                /*
                 * close the up schema and write the down schema
                 *
                 */

                $output.="});"."\n";
                $output.="}"."\n\n";
                $output.="/**\n* Reverse the migrations.\n*\n* @return void\n*/\n\n";
                $output.="public function down()"."\n";
                $output.="{"."\n";
                $output.="Schema::drop('".$tableNameLower."');"."\n";
                $output.="}"."\n";
                $output.="}"."\n";

                /*
                 * end, ready to output this table migration
                 *
                 */
                $fileName=date("Y_m_d_His")."_create-".$tableNameLower.".php";
                file_put_contents("migrations/".$fileName, $output);
                echo "saved $fileName into 'migrations/'\n\n";
                /*
                *cle the $output variable for next table
                */

                $output="<?php";
                // echo $output."\n\n";
            }
        }

    }

?>