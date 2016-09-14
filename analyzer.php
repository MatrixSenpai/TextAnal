<?php
  class Analyzer {

    private $dictionary;
    private $input;
    private $counts;
    private $fset;

    public function __construct() {
      $raw = fopen('dict.json', 'r');

      if(file_exists('sett.sysdat')) {
        $settings = json_decode(file_get_contents('sett.sysdat'));
        if(file_exists($settings['file_name'])) {
          $this->set_file($settings['file_name']);
        } else {
          $this->fset = FALSE;
        }
      } else {
        $this->fset = FALSE;
      }

      $this->dictionary = json_decode(fread($raw, filesize('dict.json')));

      $this->counts = [];
    }

    public function set_file($file) {
      $raw = file_get_contents($file);
      $this->input = explode(PHP_EOL, $raw);
      array_pop($this->input);
      $this->fset = TRUE;
    }

    public function is_file_set() {
      return $this->fset;
    }

    public function analyze() {
      foreach($this->input as $line) {
        $words = explode(" ", $line);
        if(strlen(end($words)) < 2) { array_pop($words); }
        foreach($words as $word) { $this->check($word); }
      }
      $this->statistics();
    }

    private function check($word) {
      foreach($this->dictionary as $letter => $words) {
        if(in_array($word, $words)) {
          if (array_key_exists($word, $this->counts)) { $this->counts[$word]++; } else { $this->counts[$word] = 1; }
        }
      }
    }

    private function statistics() {
      $c = $this->counts;
      $i = 0;

      foreach($c as $w) { $i += $w; }

      system("clear");

      print("---------- Statistics ----------\n");
      foreach($c as $k => $v) {
        print("{$k}: {$v}x\n");
      }
    }
  }
