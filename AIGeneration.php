<?php
// The code presented here is ONLY for application evaluation purposes for Joshua Petersen.
// Permission of any other kind for the code is not given or implied.
    require_once('CodingAI.php');
    require_once('libs/common.php');
    
    function create_species($count, $time_each, $species_name){
        $_SESSION["SPECIES"] = [];
        for ($i = 1; $i <= $count; $i++){
            $_SESSION["SPECIES"][$i] = ["SPECIES_NAME" => $species_name, "AI" => new my_little_AI($species_name . $i, [], $time_each)];
        }
    }
    
    function load_species($species_name){
        $_SESSION["SPECIES"] = select_array('SELECT * FROM `SPECIES` WHERE `SPECIES` = "' . $species_name . '";');
        foreach ($_SESSION["SPECIES"] as $AI){
            $AI["AI"] = unserialize(base64_decode($AI["AI"]));
            $AI["SPECIES_NAME"] = $species_name;
        }
    }
    
    function save_species(){
        foreach ($_SESSION["SPECIES"] as $AI){
            $AI["AI"] = base64_encode(serialize($AI["AI"]));
            insert('INSERT INTO `SPECIES` (`AI`, `SPECIES`, `TIMESTAMP`) VALUES ("' . $AI["AI"] . '", "' . $AI["SPECIES_NAME"] . '", NOW());');
        }
        select_view('DELETE FROM `AI` WHERE `TIMESTAMP` < NOW() - INTERVAL 1 DAY)');
    }
    
    function evolve_generation($input, $comparison = "", $time_limit=0, $mutation_rate = 20){
        
        $total_ranking = 0;
        $original_species_count = count($_SESSION["SPECIES"]);
        foreach ($_SESSION["SPECIES"] as $member_of_species){
            
            // random fix, sometimes AI was object, sometimes in array that contained the object.
            // just a hotfix
            if (is_object($member_of_species)){
                $myAI = $member_of_species;
            } else {
                $myAI = $member_of_species["AI"];
            }
            
            $myAI->environment = $input;
            if ($time_limit != 0){
                $myAI->allowed_time = $time_limit;
            }
            $myAI->run_AI();
            if ($comparison != ""){
                similar_text($input, $comparison, $myAI->value);
            } else {
                $myAI->value = rand(1,10);
            }
            $total_ranking += $myAI->value;
            if (is_object($member_of_species)){
                $member_of_species = $myAI;
            } else {
                $member_of_species["AI"] = $myAI;
            }
            
        }
        if ($original_species_count == 0){
            $original_species_count = 1;
        }
        $total_ranking = $total_ranking / $original_species_count;
        $this_species_name = $_SESSION["SPECIES"][array_rand($_SESSION["SPECIES"])]["SPECIES_NAME"];
        foreach($_SESSION["SPECIES"] as $key => $AI){
            if (is_object($AI)){
                $checkAI = $AI;
            } else {
                $checkAI = $AI["AI"];
            }
            $temp_value = $checkAI->value;
            // cull unhealthy AIs
            if ($temp_value <= ($total_ranking)){
                unset($_SESSION["SPECIES"][$key]);
            } elseif (!isset($checkAI->value)){
                unset($_SESSION["SPECIES"][$key]);
            }
            // cull empty AIs
            if (empty($AI["AI"]->source_code)){
                unset($_SESSION["SPECIES"][$key]);
            }
        }
        for ($i = count($_SESSION["SPECIES"]); $i < $original_species_count; $i++){
            if (count($_SESSION["SPECIES"]) == 0){
                $new_AI = breed_new_AIs_1($mutation_rate, $this_species_name);
                array_push($_SESSION["SPECIES"], ["SPECIES_NAME" => $this_species_name, "AI" => $new_AI]);
                continue;
            }
            $AI_1 = array_rand($_SESSION["SPECIES"], 1);
            $AI_2 = array_rand($_SESSION["SPECIES"], 1);
            if (is_object($_SESSION["SPECIES"][$AI_1])){
                $passAI1 = $_SESSION["SPECIES"][$AI_1];
            } else {
                $passAI1 = $_SESSION["SPECIES"][$AI_1]["AI"];
            }
            if (is_object($_SESSION["SPECIES"][$AI_2])){
                $passAI2 = $_SESSION["SPECIES"][$AI_2];
            } else {
                $passAI2 = $_SESSION["SPECIES"][$AI_2]["AI"];
            }
            $new_AI = breed_AIs_1($passAI1, $passAI2, $mutation_rate);
            array_push($_SESSION["SPECIES"], ["SPECIES_NAME" => $this_species_name, "AI" => $new_AI]);
        }
    }
    

