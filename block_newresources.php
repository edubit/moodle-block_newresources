<?php
class block_newresources extends block_base {
    public function init() {
        $this->title = get_string('newresources', 'block_newresources');
    }
    public function get_content() {
    if ($this->content !== null) {
      return $this->content;
    }
 
    $this->content         =  new stdClass;
    $this->content->text   = 'The content of our SimpleHTML block!';
    $this->content->footer = 'Footer here...';
 
    return $this->content;
  }
}   // Here's the closing bracket for the class definition