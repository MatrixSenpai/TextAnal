<?php
  use Dariuszp\CliProgressBar;

  define(DICT_PATH, './.sdata/dict.json');
  define(SETTINGS_PATH, '.sdata/settings.sysdata');

  /**
   * CLASS ANALYZER
   * Analyzes and prints statistics based on a given,
   * user-editable dictionary. Takes input from user
   * definition, and provides capability to save
   * and/or edit given file.
   */
  class Analyzer {

    private $dictionary;
    private $input;
    private $counts;
    private $fset;
    private $should;
    private $total;

    // ------------------- PUBLIC ------------
    /**
     * Constructor
     * Loads dictionary, inits count and total vars
     */
    public function __construct() {
      $raw = fopen(DICT_PATH, 'r');
      $this->dictionary = json_decode(fread($raw, filesize(DICT_PATH)));
      fclose($raw);

      if(file_exists(SETTINGS_PATH)) {
        $settings = json_decode(file_get_contents(SETTINGS_PATH));
        $this->set_file($settings->settings->file_name);
      } else {
        $this->fset = FALSE;
      }

      $this->counts = [];
      $this->total = 0;
    }

    /**
     * Destructor
     * Saves dictionary (if needed), saves user
     * defined file name (if needed)
     */
    public function __destruct() {
      if($this->should) {
        // Save logic
      }
    }

    /**
     * set_file
     * Sets the input file and check if exists. Read-only
     * @param file - input file
     */
    public function set_file($file) {
      $raw = file_get_contents($file);
      $this->input = explode(PHP_EOL, $raw);
      array_pop($this->input);
      $this->fset = TRUE;
    }

    /**
     * is_file_set
     * Returns $fset, public getter
     */
    public function is_file_set() {
      return $this->fset;
    }
    
    /**
     * save
     * Tells analyzer if files need to be saved.
     * Set automatically when a word is added or
     * removed
     * Public setter for $should
     */
    public function save() {
      $this->should = TRUE;
    }

    /**
     * analyze
     * Public analysis function
     * Simple loop for each line and word in the
     * input file
     */
    public function analyze() {
      foreach($this->input as $line) {
        $words = explode(" ", $line);
        if(strlen(end($words)) < 2) { array_pop($words); }
        foreach($words as $word) { $this->check($word); }
      }
      $this->statistics();
    }

    // --------------------- PRIVATE --------------
    /**
     * check
     * Checks each word against the dictionary
     * provided
     * Does not check against file, as this may
     * not be the most up-to-date and is only
     * updated on clean exit
     * @param word - the word to be checked against the dictionary
     */
    private function check($word) {
      $this->total++;
      foreach($this->dictionary as $letter => $words) {
        if(in_array($word, $words)) {
          if (array_key_exists($word, $this->counts)) { $this->counts[$word]++; } else { $this->counts[$word] = 1; }
        }
      }
    }

    /**
     * statistics
     * Prints statistics gathered by the
     * analyze function
     */
    private function statistics() {
      $c = $this->counts;
      $i = 0;

      foreach($c as $w) { $i += $w; }

      system("clear");

      print("---------- Statistics ----------\n");
      print("Total words: {$this->total}\n");
      foreach($c as $k => $v) {
        print("Uses of {$k}");
        $b = new CliProgressBar($this->total, $v);
        $b->display();
        $b->end();
      }
    }
  }
