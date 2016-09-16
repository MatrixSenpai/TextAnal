#!/usr/bin/php
<?php
  require_once('analyzer.php');
  require 'vendor/autoload.php';

  class MainSystem {
    private $anal;

    public function __construct() {
      $this->anal = new Analyzer;
    }

    function print_header() {
      system("clear");
      print("---------------------------------------\n");
      print("TTTTTT EEEEEE XX    XX TTTTTT            AAA     NN   NN      AAA      LL\n ");
      print(" TT   EE      XX  XX    TT             AA AA    NNN  NN     AA AA     LL\n");
      print("  TT   EEE      XXX      TT    ---     AAAAAAA   NN NN N    AAAAAAA    LL\n");
      print("  TT   EE      XX XX     TT           AA     AA  NN  NNN   AA     AA   LL\n");
      print("  TT   EEEEEE XX   XX    TT          AA       AA NN   NN  AA       AA  LLLLLL\n\n");
      $this->print_menu();
    }


    function print_menu() {
      print("Main Menu\n---------\n");
      print("1) Add word to dictionary\n");
      print("2) Remove word from dictionary\n");
      print("3) Set input file\n");
      print("4) Analyze Usage\n");
      print("5) Quit\n");

      $cmd = readline("What would you like to do? > ");
      $this->run_command($cmd);
    }

    function run_command($cmd) {
      system("clear");
      try {
        $cmd = intval($cmd);
        if($cmd < 1) { print("\nInvalid command!\n\n"); $this->print_menu(); }

        if($cmd == 1) { $this->add_word(); }
        elseif ($cmd == 2) { $this->remove_word(); }
        elseif ($cmd == 3) { $this->set_file(); }
        elseif ($cmd == 4) {
          if($this->anal->is_file_set()) { $this->anal->analyze(); } else { print("No file set!"); }
        } else { $this->end_run(); }
      } catch (Exception $e) {
        print("Error! Incorrect action! {$e}\n");
      }

      $this->print_menu();
    }

    private function add_word() {
      print("------------------ Add a word --------------------\n");
      print("Add a custom word to the dictionary\n");
      $this->anal->print_dict();
      $word = readline("Add word > ");
      $this->anal->add_word($word);
    }

    private function remove_word() {
      print("------------------ Delete a word --------------------\n");
      print("Remove a word to the dictionary\n");
      $this->anal->print_dict();
      $word = readline("Delete word > ");
      $this->anal->del_word($word);
    }

    private function set_file() {
      while(True) {
        print("Current file: {$this->anal->get_file_name()}\n");
        print("Set the input file\n");
        $path = readline("File name (including path if not in current directory) > ");
        if(!file_exists($path)) { print("\nInvalid file path!!\n"); }
        else { $this->anal->set_file($path); break; }
      }
    }

    private function end_run() {
      $c = readline("Would you like to save? [Y/n] > ");
      if(strtolower($c) == "n") { exit(); }
      $this->anal->save();
      $this->anal->__destruct();
      exit();
    }
  }

$main = new MainSystem();
$main->print_header();
?>
