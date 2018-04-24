<?php
require_once("AIGeneration.php");
$random_form_number = rand(1,99999);
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
$species_options = "";
foreach (select_array('SELECT DISTINCT `SPECIES_NAME` FROM `SPECIES`;') as $species){
    if ($species != null && !empty($species)){
        $species_options .= "<option value='" . $species["SPECIES_NAME"] . "'>" . $species["SPECIES_NAME"] . "</option>";
    } else {
       $species_options .= "<option value=''>--No species to load--</option>";
    }
}

if (isset($_POST["purpose"])){
    switch($_POST["purpose"]){
        case "create_new":
            create_species($_POST["population"], $_POST["lifespan"], $_POST["species_name"]);
        break;
        case "select_existing":
            load_species($_POST["species_name"]);
        break;
        case "breed_species":
            evolve_generation($_POST["environment_input"], $_POST["comparison_text"], $_POST["lifespan"], $_POST["mutation"]);
        break;
        case "save_existing":
            save_species();
        break;
        case "reboot":
            session_destroy();
            header("Refresh:0");
        break;
    }
}


echo <<< HTML
<!DOCTYPE HTML>
<html>
<head>
<meta charset="UTF-8">
<title>My Little Evolutionary AI</title>
<link rel="stylesheet" href="AI.css">
</head>
<body>
HTML;

echo <<< HTML
<div class="notification"><b>Notes</b>:
    <ul>
    <li>This experiment is still in alpha, and may take awhile to process. Process may end if you navigate away, progress notification not yet implemented; please be patient.</li>
    <li>Some mysqli warnings up above are expected. I'm working on a load/save feature that's not implemented yet. I'm enjoying this experiment and am still implementing. There may also be some other warnings that pop up if an AI evolves to do something that php isn't built to handle, this is fine, and they can be ignored (the AIs that produce unworking code will be culled by the next breed cycle.</li>
    <li>The goal of this project is to build an evolutionary AI based off a modified RISC-V instruction set to see the feasibility of evolving an AI using an instruction set not built for AI. Thus, eventual results are not guaranteed. I'm doing science here, and a positive result to hypothesis isn't guaranteed.</li>
    <li>Many AIs in early generation will not generate output. Generally, the first time you create your AI group, it'll start with a empty genetic code. It will rely on random mutations to get to where it actually produces output (I've got a minimal few seed values to kickstart a little, but wanted to avoid too much of a helping hand that might screw with the research.)</li>
    <li>I generally find I have to go through breeding about two or three generations of AI before they start producing code. Note, that's not code that <em>produces results</em>, but just code. </li><li>I have experimented with the program here, it <em>does</em> appear to eventually produce code that gives an output, but it takes a long time (one reason I'm wanting to add in a save feature). However, it's a slow process getting there.</li>
    <li>Again, to reiterate, this is an experiment. The vast majority of the process here was built from scratch. Going into it, I had no reason to believe if a viable program could actually be produced, and I want to find out.</li>
    <li>So far, I was updating and experimenting with it, and have, to date, only gotten one AI that produced a reasonable output (that took many hours), so I consider the experiment potentially a success, but I don't know if it was a fluke, and have been improving the code to streamline it to try and get to a positive result sooner.</li>
    <li>Note: An earlier iteration of the AI got out of control and started injecting itself into ram and started re-writing the page (it didn't produce output in the expected place, but produced output in lots of other unplanned places until I found the evolutionary algorithm I was using had a recursive memory leak; I saved that code for later investigation because it was doing some pretty neat things I want to investigate when I get the time. I had a kill switch that worked, and ended it, but if you see the phrase "DEG" come up, repeating over and over, let me know. It was really interesting, and I'd be curious to see if it survived.</li>
    <li>You might also see "case" with numerical values repeatedly appearing at the top of the screen after a successful breed cycle. This is testing code I have put there that's tracking when and what type of mutations are happening.</li>
    <li>Future plans:
        <ul>
        <li>Implement a save/load function</li>
        <li>Add additional instruction sets to choose from.</li>
        <li>Include horizontal "gene" transfer.</li>
        </ul>
    </li>
    </ul>
    <li>Notice: I just implemented a few new features, and one AI I just generated took advantage of a memory leak and crashed the process (to be fair, it was really interesting that it was able to do that.) I fixed the leak and reset the system, but you might have saw some weird errors for bit. (I had a kill switch that ended the runaway AI), and reverted to an earlier version and fixed the leak. As a note. It should be clear now, but let me know if you see the phrase "DEG" showing up repeatedly, that was a noticable part of the static parts of the code that got out. </li>
</div>

HTML;


// Creating a new species
echo <<< HTML
<div class="task_group">
<form method='post' action="AInterface.php?form_rand=$random_form_number">
<input type="hidden" name="purpose" value="create_new">
Name of your AI species: <input type="text" name="species_name" placeholder="Tachikoma" value="Tachikoma"><br />
<br />
How large will your AI population be: <input type="number" name="population" value="100"><br />
<br />
How long with their lifespans be in seconds: <input type="number" name="lifespan" value="2"><br />
<em>Note: Runtime will often be lifespan times population with additional time for breeding (about as long again)</em><br />
<button>Create</button>
</form>
</div>
HTML;
echo "<!-- ";
// Loading an existing species
echo <<< HTML
<div class="task_group">
<br />
Select an existing species of AI:<br />
<form method='post' action="AInterface.php?form_rand=$random_form_number">
<input type="hidden" name="purpose" value="select_existing">
<select name="species_name">$species_options</select>
<button>Load</button>
</form>
</div>
HTML;

echo "-->";

// Reset Page
echo <<< HTML
<div class="task_group">
<br />
<form method='post' action="AInterface.php?form_rand=$random_form_number">
Reset Page: <br />
<input type="hidden" name="purpose" value="reboot">
<button>Reboot</button>
</form>
</div>
HTML;

if (isset($_SESSION["SPECIES"])){

    echo "<!--";
    // Saving a loaded species
    echo <<< HTML
    <div class="task_group">
    <br />
    <form method='post' action="AInterface.php?form_rand=$random_form_number">
    <input type="hidden" name="purpose" value="save_existing">
    <button>Save Current AI</button>
    </form>
    </div>
HTML;

    echo "-->";
    if (isset($_POST["input_text"])) {$default_input_text = $_POST["input_text"];} else { $default_input_text = "abcdefghijklmnopqrstuvwxyz";}
    if (isset($_POST["comparison_text"])) {$default_comparison_text = $_POST["comparison_text"];} else { $default_comparison_text = "zyxwvutsrqponmlkjihgfedcba";}
    if (isset($_POST["mutation"])) {$default_mutation = $_POST["mutation"];} else { $default_mutation = 9999;}
    if (isset($_POST["lifespan"])) {$default_lifespan = $_POST["lifespan"];} else { $default_lifespan = 2;}

    // Breed a selected species
    echo <<< HTML
    <div class="task_group">
    <form method='post' action="AInterface.php?form_rand=$random_form_number">
    <input type="hidden" name="purpose" value="breed_species">
        This is the text the AI will analyse. It can be anything.<br />
    <textarea name="environment_input">$default_input_text</textarea><br />
    <br />
    This is the text the AI will try to match. It can be anything. If you leave it blank, the AI will go through a random generation.<br />
    <textarea name="comparison_text">$default_comparison_text</textarea><br />
    Lifespan: <input type="number" name="lifespan" value="$default_lifespan"> in seconds per AI<br />
    Mutation Rate: <input type="number" name="mutation" value="$default_mutation"> * .01% chance<br />
    <button>Breed AI</button> <em>(Press multiple times to iterate through more generations)</em>
    </form>
    </div>
HTML;
}


echo '<div class="results">';
if (isset($_SESSION["SPECIES"])){
    echo "CURRENT GENERATION OF " . current($_SESSION["SPECIES"])["SPECIES_NAME"] . "<br />";
    foreach ($_SESSION["SPECIES"] as $AI){
        echo '<div class="AI_OUT"><div class="AIID">AI ID:' . $AI["AI"]->ID . '</div>';
        echo '<div class="Results">Result:' . $AI["AI"]->result . '</div>';
        echo '<div class="Value">Calculated Value:' . $AI["AI"]->value . '</div>';
        echo '<div class="Results">Lifespan:<br /><em>(Start: ' . ($AI["AI"]->start_time . ' End: ' . $AI["AI"]->start_time) . 'Allowed: ' . $AI["AI"]->allowed_time . ')</em></div>';
        echo '<div class="collapse_able Start_Code">Start Code <em>Hover to expand</em>:<pre><code>' . print_r($AI["AI"]->source_code, true) . '</code></pre></div>';
        echo '<div class="collapse_able Current_Code">Current Code <em>Hover to expand</em>:<pre><code>' . print_r($AI["AI"]->source_register, true) . '</code></pre></div>';
        echo '</div><br />';
    }
}

echo '</div>';
?>
</body>
</html>
