<?php
// The code presented here is ONLY for application evaluation purposes for Joshua Petersen.
// Permission of any other kind for the code is not given or implied.

// This evolutionary AI builds its code using the the RISC-V TinyRV2 instruction set for inspiration: web.csl.cornell.edu/courses/ece5745/handouts/ece5745-tinyrv-isa.txt but is not designed to be fully compatible, and contains some additional functionality to speed up evolution of the AI to various kinds of interaction.
require_once("libs/simple_html_dom.php");
require_once("libs/get_longest_common.php");

class my_little_AI {
    public $ID;
    public $source_code = []; // Unchanged section of code for reference by AI
    public $source_register = []; // Working space in code
    private $general_register = []; // Where code runs from, mostly unchanging
    public $result = "Not evolved enough to generate its own value";
    public $start_time = 0;
    public $allowed_time = 3;
    public $end_time = "";
    private $current_step;
    public $environment = "";
    public $value = "";
    
    function __construct($AIID, $the_source_code, $allowing_time = 0, $input = ""){
        $this->ID = $AIID;
        $this->source_code = $the_source_code;
        array_push($this->source_register, ['OUT2', 'Autogen End', 0, 0, 0]);
        if ($allowing_time != 0) {
            $this->allowed_time = $allowing_time;
        }
        if ($input != "") {
            $this->environment = $input;
        }
    }
    
    function run_AI(){
        $time = time();
        $this->start_time = $time;
        $this->source_register = $this->source_code;
        while ($this->current_step < count($this->source_register)){
            try {
                if (!isset($this->source_register[$this->current_step][0])){$this->result = "This AI was was never born."; $this->end_run(); break;}
                $action = $this->source_register[$this->current_step][0];
                $static_value = $this->source_register[$this->current_step][1];
                $address_array = [$this->source_register[$this->current_step][2], $this->source_register[$this->current_step][3], $this->source_register[$this->current_step][4]];
                // Expected format for each step: ['COMD', 'somestringOrNumber', INT, INT, INT]
                instruction($action, $static_value, $address_array);
            } catch (Exception $e){
                $this->result = "This AI died of digital cancer. Genes errored out at: " . $e->getMessage(); 
            }
        }
    }
    
    function instruction($action, $static_value = "", $addresses = []){
        if ($allowed_time < time()){
            $this->result = "AI died without achieving anything in its short life.";
            end_run();
        }
        switch($action){
            case 'STATS':
                // Put static in source register
                $this->source_register[$addresses[0]] = $static_value;
            case 'STATG':
                // Put static in general register
                $this->general_register[$addresses[0]] = $static_value;
            case 'READF':
                // Read full input
                $this->general_register[$addresses[0]] = $this->environment;
            case 'READP':
                // Read partial input
                $this->general_register[$addresses[0]] = substr($this->environment, $general_register[$addresses[1]], $this->general_register[$addresses[2]]);
            case 'ADD':
                // Adding action
                $this->general_register[$addresses[2]] = $this->general_register[$addresses[0]] + $this->general_register[$addresses[1]];
                break;
            case 'ADDI':
                // Adding action
                $this->general_register[$addresses[2]] = $this->general_register[$addresses[0]] + $static_value;
                break;
            case 'SUB':
                // Subtraction action
                $this->general_register[$addresses[2]] = $this->general_register[$addresses[0]] - $this->general_register[$addresses[1]];
                break;
            case 'MUL':
                // Multiplication action
                $this->general_register[$addresses[2]] = $this->general_register[$addresses[0]] * $this->general_register[$addresses[1]];
                break;
            case 'ANDB':
                // Bitwise And action
                $this->general_register[$addresses[2]] = $this->general_register[$addresses[0]] & $this->general_register[$addresses[1]];
                break;
            case 'ANDL':
                // Logical And action
                if ($this->general_register[$addresses[0]] && $this->general_register[$addresses[1]]) {
                    $this->general_register[$addresses[2]] = true;
                } else {
                    $this->general_register[$addresses[2]] = false;
                }
                break;
            case 'ANDI':
                // Bitwise And action with static
                $this->general_register[$addresses[2]] = $this->general_register[$addresses[0]] & $static_value;
                break;
            case 'ORB':
                // Bitwise OR action
                $this->general_register[$addresses[2]] = $this->general_register[$addresses[0]] | $this->general_register[$addresses[1]];
                break;
            case 'ORL':
                // Logical OR action
                if ($this->general_register[$addresses[0]] || $this->general_register[$addresses[1]]) {
                    $this->general_register[$addresses[2]] = true;
                } else {
                    $this->general_register[$addresses[2]] = false;
                }
                break;
            case 'ORI':
                // Bitwise OR action with static
                $this->general_register[$addresses[2]] = $this->general_register[$addresses[0]] | $static_value;
                break;
            case 'XORB':
                // Bitwise XOR action
                $this->general_register[$addresses[2]] = $this->general_register[$addresses[0]] ^ $this->general_register[$addresses[1]];
                break;
            case 'XORL':
                // Logical XOR action
                if ($this->general_register[$addresses[0]] xor $this->general_register[$addresses[1]]) {
                    $this->general_register[$addresses[2]] = true;
                } else {
                    $this->general_register[$addresses[2]] = false;
                }
                break;
            case 'XORI':
                // Bitwise XOR action with constant
                $this->general_register[$addresses[2]] = $this->general_register[$addresses[0]] ^ $static_value;
                break;
            case 'SLT':
                // Compare signed results
                if ($this->general_register[$addresses[0]] < $this->general_register[$addresses[1]]) {
                    $this->general_register[$addresses[2]] = true;
                } else {
                    $this->general_register[$addresses[2]] = false;
                }
                break;
            case 'SLTI':
                // Compare signed results, assign GPR if true
                if ($this->general_register[$addresses[0]] < $static_value) {
                    $this->general_register[$addresses[0]] = $this->general_register[$addresses[1]];
                }
                break;
            case 'SLTU':
                // Compare unsigned results
                if (abs($this->general_register[$addresses[0]]) < abs($this->general_register[$addresses[1]])) {
                    $this->general_register[$addresses[2]] = true;
                } else {
                    $this->general_register[$addresses[2]] = false;
                }
                break;
            case 'SLTIU':
                // Compare signed results, assign GPR if true
                if (abs($this->general_register[$addresses[0]]) < abs($static_value)) {
                    $this->general_register[$addresses[0]] = $this->general_register[$addresses[1]];
                }
                break;
            case 'SRA':
                // Shift Right, Arithmetic
                $this->general_register[$addresses[2]] = $this->general_register[$addresses[0]] >> $this->general_register[$addresses[1]];
                break;
            case 'SRAI':
                // Shift Right, Arithmetic, with constant
                $this->general_register[$addresses[1]] = $this->general_register[$addresses[0]] >> $static_value;
                break;
            case 'SRL':
                // Shift Right, Logical
                $this->general_register[$addresses[2]] = ($this->general_register[$addresses[0]] >> $this->general_register[$addresses[1]]) & (PHP_INT_MAX >> $this->general_register[$addresses[1]] - 1);
                break;
            case 'SRLI':
                // Shift Right, Logical, with constant
                $this->general_register[$addresses[1]] = ($this->general_register[$addresses[0]] >> $static_value) & (PHP_INT_MAX >> $static_value - 1);
                break;
            case 'SLL':
                // Shift Left, Logical
                $this->general_register[$addresses[2]] = ($this->general_register[$addresses[0]] << $this->general_register[$addresses[1]]) & (PHP_INT_MAX << $this->general_register[$addresses[1]] - 1);
                break;
            case 'SLLI':
                // Shift Left, Logical, with constant
                $this->general_register[$addresses[1]] = ($this->general_register[$addresses[0]] << $static_value) & (PHP_INT_MAX << $static_value - 1);
                break;
            case 'LUI':
                // Shift left a word
                $this->general_register[$addresses[0]] = $this->general_register[$addresses[0]] << 16;
                break;
            case 'AUIPC':
                // Shift left a word based on second value
                $this->general_register[$addresses[1]] = $this->general_register[$addresses[0]] << 16;
                break;
            case 'JAL':
                // Jump to a different step, and put that position in the register
                $this->current_step = $static_value;
                $this->general_register[$addresses[0]] = $static_value;
                break;
            case 'JR':
                // Jump to a specific step
                $this->current_step = $static_value;
                break;
            case 'JALR':
                // Jump to a step and assign that step to a register
                $this->current_step = $this->general_register[$addresses[1]];
                $this->general_register[$addresses[0]] = $this->general_register[$addresses[1]];
                break;
            case 'BEQ':
                // Branch to step if values equal
                if ($this->general_register[$addresses[0]] == $this->general_register[$addresses[1]]) {
                    $this->current_step = $static_value;
                }
                break;
            case 'BNE':
                // Branch to step if values not equal
                if ($this->general_register[$addresses[0]] != $this->general_register[$addresses[1]]) {
                    $this->current_step = $static_value;
                }
                break;
            case 'BLT':
                // Branch to step if one value less than other
                if ($this->general_register[$addresses[0]] < $this->general_register[$addresses[1]]) {
                    $this->current_step = $static_value;
                }
                break;
            case 'BGE':
                // Branch to step if one value equal or greater than other
                if ($this->general_register[$addresses[0]] >= $this->general_register[$addresses[1]]) {
                    $this->current_step = $static_value;
                }
                break;
            case 'BLTU':
                // Branch to step if absolute value less than other
                if (abs($this->general_register[$addresses[0]]) < abs($this->general_register[$addresses[1]])) {
                    $this->current_step = $static_value;
                }
                break;
            case 'BGEU':
                // Branch to step if absolute value equal to or greater than other
                if (abs($this->general_register[$addresses[0]]) >= abs($this->general_register[$addresses[1]])) {
                    $this->current_step = $static_value;
                }
                break;
            case 'CSRR':
                // Add source value to register
                $this->general_register[$addresses[0]] = $this->source_register[$addresses[1]];
                break;
            case 'CSRW':
                // Add register value to source
                $this->source_register[$addresses[0]] = $this->general_register[$addresses[1]];
                break;
            case 'URL':
                // Get a URL's info
                $this->general_register[$addresses[1]] = file_get_html($this->source_register[$addresses[0]]);
            case 'OUT1':
                // Output from a source register
                $this->result = $this->source_register[$addresses[0]];
                $this->end_run();
                break;
            case 'OUT2':
                // Output from a general register
                $this->result = $this->general_register[$addresses[0]];
                $this::end_run();
                break;
        }
    }
    function end_run(){
        $end_time = time();
        $current_step = count($this->source_register);
        return $this->result;
    }
    function EOL_AI(){
        unset($this);
    }
}

function breed_new_AIs_1($mutation_rate, $species_name){
    $parentAI_1 = new my_little_AI($species_name . rand(1, 10000), [['STATG', 'Lorem Ipsum', 1, 0, 0], ['READF', 'Not Applicable', 0, 0, 0], ['OUT2', 'Autogen End', 0, 0, 0]], 2, $input = "");
    $parentAI_2 = new my_little_AI($species_name . rand(1, 10000), [['STATG', 'Lorem Ipsum', 1, 0, 0], ['READF', 'Not Applicable', 0, 0, 0], ['OUT2', 'Autogen End', 0, 0, 0]], 2, $input = "");
    return breed_AIs_1($parentAI_1, $parentAI_2, $mutation_rate);
}
 

function breed_AIs_1(&$AI_1, &$AI_2, $mutation_rate){
    //echo "<div class='task_group'><strong>Mutation Actions for bred AI:</strong>";
    $babyAI = new my_little_AI(get_longest_common_subsequence($AI_1->ID, $AI_2->ID) . rand(1,10000), [], $AI_1->allowed_time); 
    if (count($AI_1->source_code) > count ($AI_2->source_code)){
        $babyAI->source_code = breed_AIs_2($AI_1, $AI_2, $mutation_rate);
    } else {
        $babyAI->source_code = breed_AIs_2($AI_2, $AI_1, $mutation_rate);
    }
    //echo "</div>";
    return $babyAI;
}
function breed_AIs_2(&$AI_1, &$AI_2, $mutation_rate){
    $baby_AI_code = [];
    if (!is_array($AI_1->source_code)){
        $AI_1->source_code = [['STATG', 'Lorem Ipsum', 1, 0, 0], ['READF', 'Not Applicable', 0, 0, 0], ['OUT2', 'Autogen End', 0, 0, 0]];
    }
    if (!is_array($AI_2->source_code)){
        $AI_2->source_code = [['STATG', 'Lorem Ipsum', 1, 0, 0], ['READF', 'Not Applicable', 0, 0, 0], ['OUT2', 'Autogen End', 0, 0, 0]];
    }
    
    reset($AI_2->source_code);
    foreach ($AI_1->source_code as $key => $code_piece) {
        $pass_code = current($AI_2->source_code);
        $rand = rand(1,10000); 
        if ($rand < $mutation_rate){
            if(rand(1,2) == 1){
                mutation($code_piece, $baby_AI_code, $pass_code);
            } else {
                mutation($pass_code, $baby_AI_code, $code_piece);
            }
        } elseif(rand(1,2) == 1){
            if (is_array($AI_1->source_code[$key])){
                // non-mutant breeding
                array_push($code_piece, $baby_AI_code, $pass_code);
            } else {
                // It's non-viable, might as well mutate it.
                mutation($code_piece, $baby_AI_code, $pass_code);
            }
            
        } else {
            if (is_array($pass_code)){
                array_push($pass_code, $baby_AI_code, $code_piece);
            } else {
                // It's non-viable, might as well mutate it.
                mutation($code_piece, $baby_AI_code, $pass_code);
            }
        }
        next($AI_2->source_code);
    }
    return $baby_AI_code;
}

function mutation($array_1, &$target_array, $donar_array) {
    //echo "<br /><br />mutating parent 1 gene: " . print_r($array_1, true);
    //echo "<br /><br />mutating parent 2 gene: " . print_r($donar_array, true);
    
    $rand = rand(1,5);
    if (count($target_array) < 2 && $rand == 1){
        $rand = rand(2,5);
    }
    if (empty($array_1) && empty($array_2)){
        // case 5 is an injection mutation, and useful for starting off from an empty AI.
        $rand = 5;
   } elseif (empty($array_2)){
       $array_2 = $array_1;
   } elseif (empty($array_1)){
       $array_1 = $array_2;
   }
    switch($rand){
        case 1:
            //echo " :: Gene Deletion :: ";
            // case one is a deletion mutation, just skipping will result in
            // the gene being deleted.
            return;
        case 2:
            //echo " :: Genes Rearranged :: ";
            // case two is a rearrange mutation
            $position = rand(0, count($target_array));
            array_splice($target_array, $position, 0, array($array_1));
            break;
        case 3:
            //echo " :: Genetic Duplication :: ";
            // case three is a duplication mutation
            array_push($target_array, $array_1);
            array_push($target_array, $array_1);
            break;
        case 4:
            //echo "case4";
            // case four is a static rearrangement mutation... allowing interesting effects on string effects
            $srand = rand(1,4);
            $new_static = "";
            $placement = rand(0, strlen($array_1[1]));
            $length = rand(0, strlen($array_1[1]) - $placement);
            switch($srand){
                case 1:
                    //echo " :: case - Genetic String Reduction :: ";
                    //deletion
                    $newstring = substr($array_1[1], $placement, $length);
                    break;
                case 2:
                    //echo " :: case - Genetic String Recombination :: ";
                    //rearrange
                    $newstring = substr($array_1[1], 0, $placement) . substr($array_1[1], $placement + $length, strlen($array_1[1]) - ($placement + $length)) . substr($array_1[1], $placement, $length);
                    break;
                case 3:
                    //echo " :: case - Genetic String Repitition :: ";
                    //duplication
                    $newstring = $array_1[1] . substr($array_1[1], $placement, $length);
                    break;
                case 4:
                    //echo " :: case - Genetic String new Injection :: ";
                    //injection
                    $random_string_count = rand(0,255);
                    $random_string = '';
                    for ($i = 1; $i < $random_string_count; $i++){
                        $random_string .= chr(rand(0,5));
                    }        
                    $newstring = $array_1[1] . $random_string;
                    break;
            }
            if (!is_array($array_1)){ // this can happen with some rather self-destructive AI evolutions
                 mutation([], $array_1, []);
            }
            if (!isset($array_1[1])){$array_1[1] = rand(1, 1000);}
            if (!isset($array_1[2])){$array_1[2] = rand(1, 1000);}
            if (!isset($array_1[3])){$array_1[3] = rand(1, 1000);}
            if (!isset($array_1[4])){$array_1[4] = rand(1, 1000);}
            array_push($target_array, [$array_1[0], $newstring, $array_1[2], $array_1[3], $array_1[4]]);
            break;
        case 5:
            //echo " :: case - Genetic Injection :: ";
            // case five is an injection mutation that adds a new gene
            $genearray = ['STATS', 'STATG','READF', 'READP', 'ADD', 'ADDI', 'SUB', 'MUL', 'ANDB', 'ANDL', 'ANDI', 'ORB', 'ORL', 'ORI', 'XORB', 'XORL', 'XORI', 'SLT', 'SLTI', 'SLTU', 'SLTIU', 'SRA', 'SRAI', 'SRL', 'SRLI', 'SLL', 'SLLI', 'LUI', 'AUIPC', 'JAL', 'JR', 'JALR', 'BEQ', 'BNE', 'BLT', 'BGE', 'BLTU', 'BGEU', 'CSPR', 'CSRW', 'URL', 'OUT1', 'OUT2'];
            $my_gene =  $genearray[array_rand($genearray)];
            $random_string_count = rand(0,255);
            $random_string = '';
            for ($i = 1; $i < $random_string_count; $i++){
                $random_string .= chr(rand(0,255));
            }
            // Sometimes new "arrays" won't be. This is to give them a little kickstart.
            array_push($target_array, [$my_gene, $random_string, rand(0, count($target_array)), rand(0, count($target_array)), rand(0, count($target_array))]);
            break;
    }
    return;
}

