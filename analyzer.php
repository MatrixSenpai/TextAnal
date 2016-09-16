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
    private $settings;
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
        $this->settings = json_decode(file_get_contents(SETTINGS_PATH));
        $this->set_file($this->settings->settings->file_name);
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
        $data = json_encode($this->dictionary);
        $raw = fopen(DICT_PATH, 'w');
        fwrite($raw, $data);
        fclose($raw);

        $raw = fopen(SETTINGS_PATH, 'w');
        $data = json_encode($this->settings);
        fwrite($raw, $data);
        fclose($raw);
      }
    }

    /**
     * set_file
     * Sets the input file and check if exists. Read-only
     * @param file - input file
     */
    public function set_file($file) {
      $this->settings->settings->file_name = $file;
      $raw = file_get_contents($file);
      $this->input = explode(PHP_EOL, $raw);
      array_pop($this->input);
      $this->fset = TRUE;
    }

    public function get_file_name() {
      return $this->settings->settings->file_name;
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

    public function print_dict() {
      foreach($this->dictionary as $key => $words) {
        $key = strtoupper($key);
        print("- {$key}\n");
        $str = implode(", ", $words);
        print("\t{$str}\n");
      }
    }

    public function add_word($word) {
      $key = substr($word, 0, 1)[0];
      if(array_key_exists($key, $this->dictionary)) {
        array_push($this->dictionary->$key, $word);
      } else { $this->dictionary->$key = [$word]; }
      $this->save();
    }

    public function del_word($word) {
      $key = substr($word, 0, 1)[0];
      if(array_key_exists($key, $this->dictionary)) {
        $delkey = array_search($word, $this->dictionary->$key);
        unset($this->dictionary->$key[$delkey]);
      } else {
        print("Word doesn't exist!");
      }
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
        $b = new CliProgressBar($this->total, $v, "Use of \"{$k}\"\n");
        $b->display();
        $b->end();
      }
    }
  }
