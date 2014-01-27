<?php

/**
 * This class implements the response of the script sendrating.cgi
 */
class SceauSendratingResponse extends SceauDOMDocument {

  /**
   * returns true if the stream is valid and has correctly been received by Sceau, false otherwise
   * 
   * @return bool
   */
  public function isValid() {
    return $this->root->getAttribute('type') == 'OK';
  }

  /**
   * returns true if the stream encountered a fatal error, false otherwise
   * 
   * @return bool
   */
  public function hasFatalError() {
    return $this->root->tagName == 'unluck';
  }

  /**
   * returns the message given as an answer from Sceau. It matches with the error label if an error occured.
   * 
   * @return string
   */
  public function getDetail() {
    if ($this->hasFatalError())
      return $this->root->nodeValue;

    return $this->getElementsByTagName('detail')->item(0)->nodeValue;
  }

}