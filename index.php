<?php

// PHP SCRIPT BLOCK


session_start();

?>


<html><!-- DISPLAY AND HTML BLOCK -->
    <head>
        <!-- Latest jQuery -->
        <script src="http://code.jquery.com/jquery-1.11.0.min.js"></script>

        <link rel="stylesheet" href="master.css">

        <!-- Latest compiled and minified CSS -->
        <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">
    </head>
    <body>
        <div class="container">
            <div id="display-frame" class="row">
                <!-- Will insert content here via DOM -->
                <button type="button" id="btn-start" class="btn btn-lg btn-primary" onclick="start()">Let's Play!</button>

            </div>
        </div>
    </body>
</html>

<script type="text/javascript">
    // JAVASCRIPT/JQUERY BLOCK

    // locals declarations
    var word_script = 'word_retrieval.php';

    // initialize a global state object
    var state = {};

    var user_state = {}

    state.completed_words = []; // stores the id of all loaded words to avoid duplicates
    state.letter_array =    []; // turns the loaded word into an array of characters
    state.found_letters =   []; // stores all of the letters found in the letter array
    state.guessed_letters = []; // stores all of the letters that have been guessed
    state.incorrect_guesses = 0; // stores the number of guesses that have been wrong

    state.user_state = user_state;  // stores multigame user statistics

    state.user_state.games_won = 0; // games won by the user
    state.user_state.games_lost = 0; // games lost by the user


    function start(in_progress=false) { // starts the game process

        var current_word = '';

        if (in_progress)
            confirm("restart your game?"); // confirms if the user's running game should be terminated

        reset_state(); // reset all of the one-game state variables

        $.post(

            word_script, // POST to the word_script PHP script via AJAX

            JSON.stringify(state.completed_words), // send along the completed words IDs in JSON

            function( data ) {

                current_word = process_word(data); // set the current word to the data value

                $('#btn-start').hide(); // hide the original start button

                start_screen(current_word); // load the initial screen for the new game

            });

    }


    function process_word(ajax_data) {  // read in AJAX data and make data function call

        var data = JSON.parse(ajax_data); // read in the AJAX JSON-formatted data

        var word = data[1].toLowerCase(); // save the word value into a local variable
        var id = data[0]; // save the id into a local variable

        // add word to completed words
        state.completed_words.push(id); // will be sent in the next POST AJAX call

        store_letter_array(word); // saves a version of the word into an explicit character array

        return word;
    }


    function start_screen(word) { // load the start screen display

        var display = document.getElementById("display-frame");

        display.innerHTML = '<div class="centered">' +
                                    '<p>Enter your guesses below, and then press the \'Check\' button. Good luck!</p>' +generate_restart_button() + '<br/><br/>' +
                                    generate_blanks_and_letters() + "<br/>" + "<br/>" + "<br/>" +
                                    generate_input_box() +
                                    generate_check_button() +
                                    generate_user_display() +
                              '</div>';
    }

    function refresh(correct=false) { // refresh the screen after user input

        var has_won = check_win(); // check if win conditions are satisfied
        var has_lost = check_lose(); // check if lose conditions are satisfied

        if (has_won) { // executed if the user has won

            state.user_state.games_won++;

            var display = document.getElementById("display-frame");

            display.innerHTML = '<div class="centered">' +
                                    '<p>You won!</p><br/><br/>' +
                                    '<span class="lose-highlight">' + generate_blanks_and_letters() + "</span><br/>" + "<br/>" + "<br/>" +
                                    generate_restart_button() +
                                    generate_user_display() +
                                '</div>';
        } else if (has_lost) { // executed if the user has lost

            state.user_state.games_lost++;

            var display = document.getElementById("display-frame");

            display.innerHTML = '<div class="centered">' +
                                    '<p>You lost!</p><br/><br/>' +
                                    '<span class="win-highlight">' + generate_blanks_and_letters() + "</span><br/>" + "<br/>" + "<br/>" +
                                    generate_restart_button() +
                                    generate_user_display() +
                                '</div>';
        } else { // executed if neither end condition has been satisfied

            display = document.getElementById("display-frame");

            display.innerHTML = '<div class="centered">' +
                                        generate_restart_button(true) + '<br/><br/>' +
                                        generate_guess_alert(correct) +
                                        generate_blanks_and_letters() + "<br/>" + "<br/>" + "<br/>" +
                                        generate_input_box() +
                                        generate_check_button() + "<br/>" + "<br/>" +
                                        generate_guessed_letters() + "<br/>" + "<br/>" +
                                        generate_incorrect_guesses() +
                                        generate_user_display() +
                                '</div>';

        }

    }

    function reset_state() { // resets the state locals

        state.completed_words = [];
        state.letter_array =    [];
        state.found_letters =   [];
        state.guessed_letters = [];
        state.incorrect_guesses = 0;

    }

    function check_win() {

        var len = state.found_letters.length;

        for (var i = 0 ; i < len ; i++) {

            if (state.found_letters[i] == '') { // if any letter hasn't been found, no win
                return false;
            }
        }
        return true;
    }

    function check_lose() {

        return state.incorrect_guesses >= 5; // if 5 incorrect guesses, lose

    }


    function store_letter_array(word) { // store an array copy of the word for finding guesses

        var len = word.length;

        for ( var i = 0 ; i < len ; i++ ) {

            state.letter_array.push(word[i]); // push the letter into the array
            state.found_letters.push('');    // push a blank into the found letters array

        }
    }

    function check_letter() { // check if the input letter is in the word

        var letter = document.getElementById('letter-box').value; // find what the guessed letter input is

        state.guessed_letters.push(letter); // add the letter to the guessed letters

        var len = state.letter_array.length;

        var found_letter = false; // will capture if a letter was found

        for ( var i = 0 ; i < len ; i++ ) {

            if (letter == state.letter_array[i] ) { // check if the letters match
                state.found_letters[i] = state.letter_array[i]; // set the found letters value to the letter
                found_letter = true; // capture the found letter in the boolean value
            }
        }

        if (!found_letter)
            state.incorrect_guesses++; // if the letter was not found, increment the incorrect guesses

        refresh(found_letter); // call the refresh function using whether or not the letter was found

        return found_letter; // return whether or not the letter was found

    }

    function generate_user_display() {
        var won = state.user_state.games_won; // get the num of won games
        var lost = state.user_state.games_lost; // get the num of lost games

        var ratio;

        if (won == 0) { // no games won
            if (lost == 0) { // no games won, no games lost
                ratio = "<span class='glyphicon glyphicon-remove'></span>";
            } else { // no games won, some lost
                ratio = 0 + "% won";
            }
        }  else { // some games won
            var win_perc = (won / (won + lost)) * 100;
            ratio = win_perc + "% won" ;
        }

        return "<table class='user-display'>" +
                    "<tr><td>Games won:</td><td>" + won +  "</td></tr>" +
                    "<tr><td>Games lost:</td><td>" + lost +  "</td></tr>" +
                    "<tr><td>Your win ratio:</td><td>" +  ratio  +  "</td></tr>" +
            "</table>";
    }

    function generate_blanks_and_letters() {

        var len = state.found_letters.length;

        var output_html = ""; // captures the output html to display

        for (var i = 0; i < len ; i++ ) {

            if (state.found_letters[i] != '') { // if the array has a letter in it, not a blank

                output_html += "<span>" + state.found_letters[i] + "</span>"; // add the found letters to the display

            } else {    // if the array space is blank

                output_html += "<span class='glyphicon glyphicon-minus'></span>"; // add the blank letter

            }
        }

        return output_html; // return the output html

    }

    function generate_guessed_letters() {

        var output_html = ""; // captures the output html to display

        var len = state.guessed_letters.length;

        for (var i = 0; i < len ; i++ ) {  // cycle through each guessed letter

            output_html += state.guessed_letters[i] + " "; // display what characters are displayed

            if ( i % 5 == 0  && i != 0) {   // input a break every five letters for formatting
                output_html += "<br/>";
            }
        }

        return output_html;  // return the output html
    }

    function generate_incorrect_guesses() {

        var incorrect = state.incorrect_guesses;  // find the number of incorrect guesses


        return "<span class='wrong-highlight'> Incorrect Guesses: " + incorrect + "</span>"; // return display html for the incorrect guesses

    }

    function generate_input_box() {
        // creates the unique input box
        return "<input type='text' name='guess' maxlength='1' autofocus='autofocus' class='small-input' onfocus=\"this.value=''; this.style.color='#000000'\" id='letter-box'>"
    }
    function generate_check_button() {
        // generates the check button for new letters
        return "<button type='button' class='btn btn-lg btn-warning' onclick='check_letter()'>Check Letter</button>";
    }

    function generate_restart_button(in_progress=false) {
        // generate the reset button used to reset the game depending on whether or not the game is in progress
       return '<button type="button" name="btn-start" class="btn btn-lg btn-warning" onclick="start(' + in_progress + ')">Start a New Game</button>';
    }

    function generate_guess_alert(correct=false) {

        // returns the alert depending on whether or not the answer was correct
        var correct_alert = "<div class='alert alert-success'><strong>Correct!</strong> Guess another letter!</div>"
        var incorrect_alert = "<div class='alert alert-danger'><strong>Incorrect!</strong> Guess another letter!</div>"

        // ternary op
        return correct ? correct_alert : incorrect_alert;

    }

</script>

<?php

// PHP FUNCTION BLOCK


?>

