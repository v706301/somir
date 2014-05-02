  // Automatic calculation for the following K_PATH_URL constant
  if(isset($_SERVER['HTTP_HOST']) AND (!empty($_SERVER['HTTP_HOST']))){
    if(isset($_SERVER['HTTPS']) AND (!empty($_SERVER['HTTPS'])) AND strtolower($_SERVER['HTTPS'])!='off')
      $k_path_url = 'https://';
    else
      $k_path_url = 'http://';
    $k_path_url .= $_SERVER['HTTP_HOST'];
    $k_path_url .= str_replace( '\\', '/', substr($_SERVER['PHP_SELF'], 0, -24));
  }

  /**
   * URL path to tcpdf installation folder (http://localhost/tcpdf/).
   * By default it is automatically calculated but you can also set it as a fixed string to improve performances.
   */
  define ('K_PATH_URL', $k_path_url);










    /**
     * Allows to preserve some HTML formatting (limited support).<br />
     * IMPORTANT: The HTML must be well formatted - try to clean-up it using an application like HTML-Tidy before submitting.
     * Supported tags are: a, b, blockquote, br, dd, del, div, dl, dt, em, font, h1, h2, h3, h4, h5, h6, hr, i, img, li, ol, p, small, span, strong, sub, sup, table, td, th, tr, u, ul,
     * @param string $html text to display
     * @param boolean $ln if true add a new line after text (default = true)
     * @param int $fill Indicates if the background must be painted (true) or transparent (false).
     * @param boolean $reseth if true reset the last cell height (default false).
     * @param boolean $cell if true add the default cMargin space to each Write (default false).
     * @param string $align Allows to center or align the text. Possible values are:<ul><li>L : left align</li><li>C : center</li><li>R : right align</li><li>'' : empty string : left for LTR or right for RTL</li></ul>
     * @access public
     */
    public function writeHTML($html, $ln=true, $fill=false, $reseth=false, $cell=false, $align='') {
      $startliney = null;
      $gvars = $this->getGraphicVars();
      // store current values
      $prevPage = $this->page;
      $prevlMargin = $this->lMargin;
      $prevrMargin = $this->rMargin;
      $curfontname = $this->FontFamily;
      $curfontstyle = $this->FontStyle;
      $curfontsize = $this->FontSizePt;
      $this->newline = true;
      $minstartliney = $this->y;
      $yshift = 0;
      $startlinepage = $this->page;
      $newline = true;
      $loop = 0;
      $curpos = 0;
      $blocktags = array('blockquote','br','dd','div','dt','h1','h2','h3','h4','h5','h6','hr','li','ol','p','ul');
      $this->premode = false;
      if (isset($this->PageAnnots[$this->page])) {
        $pask = count($this->PageAnnots[$this->page]);
      } else {
        $pask = 0;
      }
      if (isset($this->footerlen[$this->page])) {
        $this->footerpos[$this->page] = $this->pagelen[$this->page] - $this->footerlen[$this->page];
      } else {
        $this->footerpos[$this->page] = $this->pagelen[$this->page];
      }
      $startlinepos = $this->footerpos[$this->page];
      $lalign = $align;
      $plalign = $align;
      if ($this->rtl) {
        $w = $this->x - $this->lMargin;
      } else {
        $w = $this->w - $this->rMargin - $this->x;
      }
      $w -= (2 * $this->cMargin);
      if ($cell) {
        if ($this->rtl) {
          $this->x -= $this->cMargin;
        } else {
          $this->x += $this->cMargin;
        }
      }
      if ($this->customlistindent >= 0) {
        $this->listindent = $this->customlistindent;
      } else {
        $this->listindent = $this->GetStringWidth('0000');
      }
      $this->listnum = 0;
      if (($this->empty_string($this->lasth)) OR ($reseth)) {
        //set row height
        $this->lasth = $this->FontSize * $this->cell_height_ratio;
      }
      $dom = $this->getHtmlDomArray($html);
      $maxel = count($dom);
      $key = 0;
      while ($key < $maxel) {
        if ($dom[$key]['tag'] OR ($key == 0)) {
          if ((($dom[$key]['value'] == 'table') OR ($dom[$key]['value'] == 'tr')) AND (isset($dom[$key]['align']))) {
            $dom[$key]['align'] = ($this->rtl) ? 'R' : 'L';
          }
          // vertically align image in line
          if ((!$this->newline)
            AND ($dom[$key]['value'] == 'img')
            AND (isset($dom[$key]['attribute']['height']))
            AND ($dom[$key]['attribute']['height'] > 0)
            AND (!((($this->y + $this->getHTMLUnitToUnits($dom[$key]['attribute']['height'], $this->lasth, 'px')) > $this->PageBreakTrigger)
              AND (!$this->InFooter)
              AND $this->AcceptPageBreak()))
            ) {
            if ($this->page > $startlinepage) {
              // fix lines splitted over two pages
              if (isset($this->footerlen[$startlinepage])) {
                $curpos = $this->pagelen[$startlinepage] - $this->footerlen[$startlinepage];
              }
              // line to be moved one page forward
              $pagebuff = $this->getPageBuffer($startlinepage);
              $linebeg = substr($pagebuff, $startlinepos, ($curpos - $startlinepos));
              $tstart = substr($pagebuff, 0, $startlinepos);
              $tend = substr($this->getPageBuffer($startlinepage), $curpos);
              // remove line start from previous page
              $this->setPageBuffer($startlinepage, $tstart.''.$tend);
              $pagebuff = $this->getPageBuffer($this->page);
              $tstart = substr($pagebuff, 0, $this->intmrk[$this->page]);
              $tend = substr($pagebuff, $this->intmrk[$this->page]);
              // add line start to current page
              $yshift = $minstartliney - $this->y;
              $try = sprintf('1 0 0 1 0 %.3F cm', ($yshift * $this->k));
              $this->setPageBuffer($this->page, $tstart."\nq\n".$try."\n".$linebeg."\nQ\n".$tend);
              // shift the annotations and links
              if (isset($this->PageAnnots[$startlinepage])) {
                foreach ($this->PageAnnots[$startlinepage] as $pak => $pac) {
                  if ($pak >= $pask) {
                    $this->PageAnnots[$this->page][] = $pac;
                    unset($this->PageAnnots[$startlinepage][$pak]);
                    $npak = count($this->PageAnnots[$this->page]) - 1;
                    $this->PageAnnots[$this->page][$npak]['y'] -= $yshift;
                  }
                }
              }
            }
            $this->y += (($curfontsize / $this->k) - $this->getHTMLUnitToUnits($dom[$key]['attribute']['height'], $this->lasth, 'px'));
            $minstartliney = min($this->y, $minstartliney);
          } elseif (isset($dom[$key]['fontname']) OR isset($dom[$key]['fontstyle']) OR isset($dom[$key]['fontsize'])) {
            // account for different font size
            $pfontname = $curfontname;
            $pfontstyle = $curfontstyle;
            $pfontsize = $curfontsize;
            $fontname = isset($dom[$key]['fontname']) ? $dom[$key]['fontname'] : $curfontname;
            $fontstyle = isset($dom[$key]['fontstyle']) ? $dom[$key]['fontstyle'] : $curfontstyle;
            $fontsize = isset($dom[$key]['fontsize']) ? $dom[$key]['fontsize'] : $curfontsize;
            if (($fontname != $curfontname) OR ($fontstyle != $curfontstyle) OR ($fontsize != $curfontsize)) {
              $this->SetFont($fontname, $fontstyle, $fontsize);
              $this->lasth = $this->FontSize * $this->cell_height_ratio;
              if (is_numeric($fontsize) AND ($fontsize > 0)
                AND is_numeric($curfontsize) AND ($curfontsize > 0)
                AND ($fontsize != $curfontsize) AND (!$this->newline)
                AND ($key < ($maxel - 1))
                ) {
                if ((!$this->newline) AND ($this->page > $startlinepage)) {
                  // fix lines splitted over two pages
                  if (isset($this->footerlen[$startlinepage])) {
                    $curpos = $this->pagelen[$startlinepage] - $this->footerlen[$startlinepage];
                  }
                  // line to be moved one page forward
                  $pagebuff = $this->getPageBuffer($startlinepage);
                  $linebeg = substr($pagebuff, $startlinepos, ($curpos - $startlinepos));
                  $tstart = substr($pagebuff, 0, $startlinepos);
                  $tend = substr($this->getPageBuffer($startlinepage), $curpos);
                  // remove line start from previous page
                  $this->setPageBuffer($startlinepage, $tstart.''.$tend);
                  $pagebuff = $this->getPageBuffer($this->page);
                  $tstart = substr($pagebuff, 0, $this->intmrk[$this->page]);
                  $tend = substr($pagebuff, $this->intmrk[$this->page]);
                  // add line start to current page
                  $yshift = $minstartliney - $this->y;
                  $try = sprintf('1 0 0 1 0 %.3F cm', ($yshift * $this->k));
                  $this->setPageBuffer($this->page, $tstart."\nq\n".$try."\n".$linebeg."\nQ\n".$tend);
                  // shift the annotations and links
                  if (isset($this->PageAnnots[$startlinepage])) {
                    foreach ($this->PageAnnots[$startlinepage] as $pak => $pac) {
                      if ($pak >= $pask) {
                        $this->PageAnnots[$this->page][] = $pac;
                        unset($this->PageAnnots[$startlinepage][$pak]);
                        $npak = count($this->PageAnnots[$this->page]) - 1;
                        $this->PageAnnots[$this->page][$npak]['y'] -= $yshift;
                      }
                    }
                  }
                }
                $this->y += (($curfontsize - $fontsize) / $this->k);
                $minstartliney = min($this->y, $minstartliney);
              }
              $curfontname = $fontname;
              $curfontstyle = $fontstyle;
              $curfontsize = $fontsize;
            }
          }
          if (($plalign == 'J') AND (in_array($dom[$key]['value'], $blocktags))) {
            $plalign = '';
          }
          // get current position on page buffer
          $curpos = $this->pagelen[$startlinepage];
          if (isset($dom[$key]['bgcolor']) AND ($dom[$key]['bgcolor'] !== false)) {
            $this->SetFillColorArray($dom[$key]['bgcolor']);
            $wfill = true;
          } else {
            $wfill = $fill | false;
          }
          if (isset($dom[$key]['fgcolor']) AND ($dom[$key]['fgcolor'] !== false)) {
            $this->SetTextColorArray($dom[$key]['fgcolor']);
          }
          if (isset($dom[$key]['align'])) {
            $lalign = $dom[$key]['align'];
          }
          if ($this->empty_string($lalign)) {
            $lalign = $align;
          }
        }
        // align lines
        if ($this->newline AND (strlen($dom[$key]['value']) > 0) AND ($dom[$key]['value'] != 'td') AND ($dom[$key]['value'] != 'th')) {
          $newline = true;
          // we are at the beginning of a new line
          if (isset($startlinex)) {
            $yshift = $minstartliney - $startliney;
            if (($yshift > 0) OR ($this->page > $startlinepage)) {
              $yshift = 0;
            }
            if ((isset($plalign) AND ((($plalign == 'C') OR ($plalign == 'J') OR (($plalign == 'R') AND (!$this->rtl)) OR (($plalign == 'L') AND ($this->rtl))))) OR ($yshift < 0)) {
              // the last line must be shifted to be aligned as requested
              $linew = abs($this->endlinex - $startlinex);
              $pstart = substr($this->getPageBuffer($startlinepage), 0, $startlinepos);
              if (isset($opentagpos) AND isset($this->footerlen[$startlinepage]) AND (!$this->InFooter)) {
                $this->footerpos[$startlinepage] = $this->pagelen[$startlinepage] - $this->footerlen[$startlinepage];
                $midpos = min($opentagpos, $this->footerpos[$startlinepage]);
              } elseif (isset($opentagpos)) {
                $midpos = $opentagpos;
              } elseif (isset($this->footerlen[$startlinepage]) AND (!$this->InFooter)) {
                $this->footerpos[$startlinepage] = $this->pagelen[$startlinepage] - $this->footerlen[$startlinepage];
                $midpos = $this->footerpos[$startlinepage];
              } else {
                $midpos = 0;
              }
              if ($midpos > 0) {
                $pmid = substr($this->getPageBuffer($startlinepage), $startlinepos, ($midpos - $startlinepos));
                $pend = substr($this->getPageBuffer($startlinepage), $midpos);
              } else {
                $pmid = substr($this->getPageBuffer($startlinepage), $startlinepos);
                $pend = '';
              }
              // calculate shifting amount
              $tw = $w;
              if ($this->lMargin != $prevlMargin) {
                $tw += ($prevlMargin - $this->lMargin);
              }
              if ($this->rMargin != $prevrMargin) {
                $tw += ($prevrMargin - $this->rMargin);
              }
              $mdiff = abs($tw - $linew);
              $t_x = 0;
              if ($plalign == 'C') {
                if ($this->rtl) {
                  $t_x = -($mdiff / 2);
                } else {
                  $t_x = ($mdiff / 2);
                }
              } elseif (($plalign == 'R') AND (!$this->rtl)) {
                // right alignment on LTR document
                $t_x = $mdiff;
              } elseif (($plalign == 'L') AND ($this->rtl)) {
                // left alignment on RTL document
                $t_x = -$mdiff;
              } elseif (($plalign == 'J') AND ($plalign == $lalign)) {
                // Justification
                if ($this->rtl OR $this->tmprtl) {
                  $t_x = $this->lMargin - $this->endlinex;
                }
                $no = 0;
                $ns = 0;
                $pmidtemp = $pmid;
                // escape special characters
                $pmidtemp = preg_replace('/[\\\][\(]/x', '\\#!#OP#!#', $pmidtemp);
                $pmidtemp = preg_replace('/[\\\][\)]/x', '\\#!#CP#!#', $pmidtemp);
                // search spaces
                if (preg_match_all('/\[\(([^\)]*)\)\]/x', $pmidtemp, $lnstring, PREG_PATTERN_ORDER)) {
                  $maxkk = count($lnstring[1]) - 1;
                  for ($kk=0; $kk <= $maxkk; ++$kk) {
                    // restore special characters
                    $lnstring[1][$kk] = str_replace('#!#OP#!#', '(', $lnstring[1][$kk]);
                    $lnstring[1][$kk] = str_replace('#!#CP#!#', ')', $lnstring[1][$kk]);
                    if ($kk == $maxkk) {
                      if ($this->rtl OR $this->tmprtl) {
                        $tvalue = ltrim($lnstring[1][$kk]);
                      } else {
                        $tvalue = rtrim($lnstring[1][$kk]);
                      }
                    } else {
                      $tvalue = $lnstring[1][$kk];
                    }
                    // count spaces on line
                    $no += substr_count($lnstring[1][$kk], chr(32));
                    $ns += substr_count($tvalue, chr(32));
                  }
                  if ($this->rtl OR $this->tmprtl) {
                    $t_x = $this->lMargin - $this->endlinex - (($no - $ns - 1) * $this->GetStringWidth(chr(32)));
                  }
                  // calculate additional space to add to each space
                  $spacewidth = (($tw - $linew + (($no - $ns) * $this->GetStringWidth(chr(32)))) / ($ns?$ns:1)) * $this->k;
                  $spacewidthu = ($tw - $linew + ($no * $this->GetStringWidth(chr(32)))) / ($ns?$ns:1) / $this->FontSize / $this->k;
                  $nsmax = $ns;
                  $ns = 0;
                  reset($lnstring);
                  $offset = 0;
                  $strcount = 0;
                  $prev_epsposbeg = 0;
                  global $spacew;
                  while (preg_match('/([0-9\.\+\-]*)[\s](Td|cm|m|l|c|re)[\s]/x', $pmid, $strpiece, PREG_OFFSET_CAPTURE, $offset) == 1) {
                    if ($this->rtl OR $this->tmprtl) {
                      $spacew = ($spacewidth * ($nsmax - $ns));
                    } else {
                      $spacew = ($spacewidth * $ns);
                    }
                    $offset = $strpiece[2][1] + strlen($strpiece[2][0]);
                    $epsposbeg = strpos($pmid, 'q'.$this->epsmarker, $offset);
                    $epsposend = strpos($pmid, $this->epsmarker.'Q', $offset) + strlen($this->epsmarker.'Q');
                    if ((($epsposbeg > 0) AND ($epsposend > 0) AND ($offset > $epsposbeg) AND ($offset < $epsposend))
                      OR (($epsposbeg === false) AND ($epsposend > 0) AND ($offset < $epsposend))) {
                      // shift EPS images
                      $trx = sprintf('1 0 0 1 %.3F 0 cm', $spacew);
                      $epsposbeg = strpos($pmid, 'q'.$this->epsmarker, ($prev_epsposbeg - 6));
                      $pmid_b = substr($pmid, 0, $epsposbeg);
                      $pmid_m = substr($pmid, $epsposbeg, ($epsposend - $epsposbeg));
                      $pmid_e = substr($pmid, $epsposend);
                      $pmid = $pmid_b."\nq\n".$trx."\n".$pmid_m."\nQ\n".$pmid_e;
                      $offset = $epsposend;
                      continue;
                    }
                    $prev_epsposbeg = $epsposbeg;
                    $currentxpos = 0;
                    // shift blocks of code
                    switch ($strpiece[2][0]) {
                      case 'Td':
                      case 'cm':
                      case 'm':
                      case 'l': {
                        // get current X position
                        preg_match('/([0-9\.\+\-]*)[\s]('.$strpiece[1][0].')[\s]('.$strpiece[2][0].')([\s]*)/x', $pmid, $xmatches);
                        $currentxpos = $xmatches[1];
                        if (($strcount <= $maxkk) AND ($strpiece[2][0] == 'Td')) {
                          if ($strcount == $maxkk) {
                            if ($this->rtl OR $this->tmprtl) {
                              $tvalue = $lnstring[1][$strcount];
                            } else {
                              $tvalue = rtrim($lnstring[1][$strcount]);
                            }
                          } else {
                            $tvalue = $lnstring[1][$strcount];
                          }
                          $ns += substr_count($tvalue, chr(32));
                          ++$strcount;
                        }
                        if ($this->rtl OR $this->tmprtl) {
                          $spacew = ($spacewidth * ($nsmax - $ns));
                        }
                        // justify block
                        $pmid = preg_replace_callback('/([0-9\.\+\-]*)[\s]('.$strpiece[1][0].')[\s]('.$strpiece[2][0].')([\s]*)/x',
                          create_function('$matches', 'global $spacew;
                          $newx = sprintf("%.2F",(floatval($matches[1]) + $spacew));
                          return "".$newx." ".$matches[2]." x*#!#*x".$matches[3].$matches[4];'), $pmid, 1);
                        break;
                      }
                      case 're': {
                        // get current X position
                        preg_match('/([0-9\.\+\-]*)[\s]([0-9\.\+\-]*)[\s]([0-9\.\+\-]*)[\s]('.$strpiece[1][0].')[\s]('.$strpiece[2][0].')([\s]*)/x', $pmid, $xmatches);
                        $currentxpos = $xmatches[1];
                        // justify block
                        $pmid = preg_replace_callback('/([0-9\.\+\-]*)[\s]([0-9\.\+\-]*)[\s]([0-9\.\+\-]*)[\s]('.$strpiece[1][0].')[\s]('.$strpiece[2][0].')([\s]*)/x',
                          create_function('$matches', 'global $spacew;
                          $newx = sprintf("%.2F",(floatval($matches[1]) + $spacew));
                          return "".$newx." ".$matches[2]." ".$matches[3]." ".$matches[4]." x*#!#*x".$matches[5].$matches[6];'), $pmid, 1);
                        break;
                      }
                      case 'c': {
                        // get current X position
                        preg_match('/([0-9\.\+\-]*)[\s]([0-9\.\+\-]*)[\s]([0-9\.\+\-]*)[\s]([0-9\.\+\-]*)[\s]([0-9\.\+\-]*)[\s]('.$strpiece[1][0].')[\s]('.$strpiece[2][0].')([\s]*)/x', $pmid, $xmatches);
                        $currentxpos = $xmatches[1];
                        // justify block
                        $pmid = preg_replace_callback('/([0-9\.\+\-]*)[\s]([0-9\.\+\-]*)[\s]([0-9\.\+\-]*)[\s]([0-9\.\+\-]*)[\s]([0-9\.\+\-]*)[\s]('.$strpiece[1][0].')[\s]('.$strpiece[2][0].')([\s]*)/x',
                          create_function('$matches', 'global $spacew;
                          $newx1 = sprintf("%.3F",(floatval($matches[1]) + $spacew));
                          $newx2 = sprintf("%.3F",(floatval($matches[3]) + $spacew));
                          $newx3 = sprintf("%.3F",(floatval($matches[5]) + $spacew));
                          return "".$newx1." ".$matches[2]." ".$newx2." ".$matches[4]." ".$newx3." ".$matches[6]." x*#!#*x".$matches[7].$matches[8];'), $pmid, 1);
                        break;
                      }
                    }
                    // shift the annotations and links
                    if (isset($this->PageAnnots[$this->page])) {
                      foreach ($this->PageAnnots[$this->page] as $pak => $pac) {
                        if (($pac['y'] >= $minstartliney) AND (($pac['x'] * $this->k) >= ($currentxpos - $this->feps)) AND (($pac['x'] * $this->k) <= ($currentxpos + $this->feps))) {
                          $this->PageAnnots[$this->page][$pak]['x'] += ($spacew / $this->k);
                          $this->PageAnnots[$this->page][$pak]['w'] += (($spacewidth * $pac['numspaces']) / $this->k);
                          break;
                        }
                      }
                    }
                  } // end of while
                  // remove markers
                  $pmid = str_replace('x*#!#*x', '', $pmid);
                  if (($this->CurrentFont['type'] == 'TrueTypeUnicode') OR ($this->CurrentFont['type'] == 'cidfont0')) {
                    // multibyte characters
                    $spacew = $spacewidthu;
                    $pmidtemp = $pmid;
                    // escape special characters
                    $pmidtemp = preg_replace('/[\\\][\(]/x', '\\#!#OP#!#', $pmidtemp);
                    $pmidtemp = preg_replace('/[\\\][\)]/x', '\\#!#CP#!#', $pmidtemp);
                    $pmid = preg_replace_callback("/\[\(([^\)]*)\)\]/x",
                          create_function('$matches', 'global $spacew;
                          $matches[1] = str_replace("#!#OP#!#", "(", $matches[1]);
                          $matches[1] = str_replace("#!#CP#!#", ")", $matches[1]);
                          return "[(".str_replace(chr(0).chr(32), ") ".(-2830 * $spacew)." (", $matches[1]).")]";'), $pmidtemp);
                    $this->setPageBuffer($startlinepage, $pstart."\n".$pmid."\n".$pend);
                    $endlinepos = strlen($pstart."\n".$pmid."\n");
                  } else {
                    // non-unicode (single-byte characters)
                    $rs = sprintf("%.3F Tw", $spacewidth);
                    $pmid = preg_replace("/\[\(/x", $rs.' [(', $pmid);
                    $this->setPageBuffer($startlinepage, $pstart."\n".$pmid."\nBT 0 Tw ET\n".$pend);
                    $endlinepos = strlen($pstart."\n".$pmid."\nBT 0 Tw ET\n");
                  }
                }
              } // end of J
              if (($t_x != 0) OR ($yshift < 0)) {
                // shift the line
                $trx = sprintf('1 0 0 1 %.3F %.3F cm', ($t_x * $this->k), ($yshift * $this->k));
                $this->setPageBuffer($startlinepage, $pstart."\nq\n".$trx."\n".$pmid."\nQ\n".$pend);
                $endlinepos = strlen($pstart."\nq\n".$trx."\n".$pmid."\nQ\n");
                // shift the annotations and links
                if (isset($this->PageAnnots[$this->page])) {
                  foreach ($this->PageAnnots[$this->page] as $pak => $pac) {
                    if ($pak >= $pask) {
                      $this->PageAnnots[$this->page][$pak]['x'] += $t_x;
                      $this->PageAnnots[$this->page][$pak]['y'] -= $yshift;
                    }
                  }
                }
                $this->y -= $yshift;
              }
            }
          }
          $this->newline = false;
          $pbrk = $this->checkPageBreak($this->lasth);
          $this->SetFont($fontname, $fontstyle, $fontsize);
          if ($wfill) {
            $this->SetFillColorArray($this->bgcolor);
          }
          $startlinex = $this->x;
          $startliney = $this->y;
          $minstartliney = $this->y;
          $startlinepage = $this->page;
          if (isset($endlinepos) AND (!$pbrk)) {
            $startlinepos = $endlinepos;
            unset($endlinepos);
          } else {
            if (isset($this->footerlen[$this->page])) {
              $this->footerpos[$this->page] = $this->pagelen[$this->page] - $this->footerlen[$this->page];
            } else {
              $this->footerpos[$this->page] = $this->pagelen[$this->page];
            }
            $startlinepos = $this->footerpos[$this->page];
          }
          $plalign = $lalign;
          if (isset($this->PageAnnots[$this->page])) {
            $pask = count($this->PageAnnots[$this->page]);
          } else {
            $pask = 0;
          }
        }
        if (isset($opentagpos)) {
          unset($opentagpos);
        }
        if ($dom[$key]['tag']) {
          if ($dom[$key]['opening']) {
            if ($dom[$key]['value'] == 'table') {
              if ($this->rtl) {
                $wtmp = $this->x - $this->lMargin;
              } else {
                $wtmp = $this->w - $this->rMargin - $this->x;
              }
              $wtmp -= (2 * $this->cMargin);
              // calculate cell width
              if (isset($dom[$key]['width'])) {
                $table_width = $this->getHTMLUnitToUnits($dom[$key]['width'], $wtmp, 'px');
              } else {
                $table_width = $wtmp;
              }
            }
            // table content is handled in a special way
            if (($dom[$key]['value'] == 'td') OR ($dom[$key]['value'] == 'th')) {
              $trid = $dom[$key]['parent'];
              $table_el = $dom[$trid]['parent'];
              if (!isset($dom[$table_el]['cols'])) {
                $dom[$table_el]['cols'] = $trid['cols'];
              }
              $oldmargin = $this->cMargin;
              if (isset($dom[($dom[$trid]['parent'])]['attribute']['cellpadding'])) {
                $currentcmargin = $this->getHTMLUnitToUnits($dom[($dom[$trid]['parent'])]['attribute']['cellpadding'], 1, 'px');
              } else {
                $currentcmargin = 0;
              }
              $this->cMargin = $currentcmargin;
              if (isset($dom[($dom[$trid]['parent'])]['attribute']['cellspacing'])) {
                $cellspacing = $this->getHTMLUnitToUnits($dom[($dom[$trid]['parent'])]['attribute']['cellspacing'], 1, 'px');
              } else {
                $cellspacing = 0;
              }
              if ($this->rtl) {
                $cellspacingx = -$cellspacing;
              } else {
                $cellspacingx = $cellspacing;
              }
              $colspan = $dom[$key]['attribute']['colspan'];
              $wtmp = ($colspan * ($table_width / $dom[$table_el]['cols']));
              if (isset($dom[$key]['width'])) {
                $cellw = $this->getHTMLUnitToUnits($dom[$key]['width'], $wtmp, 'px');
              } else {
                $cellw = $wtmp;
              }
              if (isset($dom[$key]['height'])) {
                // minimum cell height
                $cellh = $this->getHTMLUnitToUnits($dom[$key]['height'], 0, 'px');
              } else {
                $cellh = 0;
              }
              $cellw -= $cellspacing;
              if (isset($dom[$key]['content'])) {
                $cell_content = $dom[$key]['content'];
              } else {
                $cell_content = '&nbsp;';
              }
              $tagtype = $dom[$key]['value'];
              $parentid = $key;
              while (($key < $maxel) AND (!(($dom[$key]['tag']) AND (!$dom[$key]['opening']) AND ($dom[$key]['value'] == $tagtype) AND ($dom[$key]['parent'] == $parentid)))) {
                // move $key index forward
                ++$key;
              }
              if (!isset($dom[$trid]['startpage'])) {
                $dom[$trid]['startpage'] = $this->page;
              } else {
                $this->setPage($dom[$trid]['startpage']);
              }
              if (!isset($dom[$trid]['starty'])) {
                $dom[$trid]['starty'] = $this->y;
              } else {
                $this->y = $dom[$trid]['starty'];
              }
              if (!isset($dom[$trid]['startx'])) {
                $dom[$trid]['startx'] = $this->x;
              }
              $this->x += ($cellspacingx / 2);
              if (isset($dom[$parentid]['attribute']['rowspan'])) {
                $rowspan = intval($dom[$parentid]['attribute']['rowspan']);
              } else {
                $rowspan = 1;
              }
              // skip row-spanned cells started on the previous rows
              if (isset($dom[$table_el]['rowspans'])) {
                $rsk = 0;
                $rskmax = count($dom[$table_el]['rowspans']);
                while ($rsk < $rskmax) {
                  $trwsp = $dom[$table_el]['rowspans'][$rsk];
                  $rsstartx = $trwsp['startx'];
                  $rsendx = $trwsp['endx'];
                  // account for margin changes
                  if ($trwsp['startpage'] < $this->page) {
                    if (($this->rtl) AND ($this->pagedim[$this->page]['orm'] != $this->pagedim[$trwsp['startpage']]['orm'])) {
                      $dl = ($this->pagedim[$this->page]['orm'] - $this->pagedim[$trwsp['startpage']]['orm']);
                      $rsstartx -= $dl;
                      $rsendx -= $dl;
                    } elseif ((!$this->rtl) AND ($this->pagedim[$this->page]['olm'] != $this->pagedim[$trwsp['startpage']]['olm'])) {
                      $dl = ($this->pagedim[$this->page]['olm'] - $this->pagedim[$trwsp['startpage']]['olm']);
                      $rsstartx += $dl;
                      $rsendx += $dl;
                    }
                  }
                  if  (($trwsp['rowspan'] > 0)
                    AND ($rsstartx > ($this->x - $cellspacing - $currentcmargin - $this->feps))
                    AND ($rsstartx < ($this->x + $cellspacing + $currentcmargin + $this->feps))
                    AND (($trwsp['starty'] < ($this->y - $this->feps)) OR ($trwsp['startpage'] < $this->page))) {
                    // set the starting X position of the current cell
                    $this->x = $rsendx + $cellspacingx;
                    if (($trwsp['rowspan'] == 1)
                      AND (isset($dom[$trid]['endy']))
                      AND (isset($dom[$trid]['endpage']))
                      AND ($trwsp['endpage'] == $dom[$trid]['endpage'])) {
                      // set ending Y position for row
                      $dom[$table_el]['rowspans'][$rsk]['endy'] = max($dom[$trid]['endy'], $trwsp['endy']);
                      $dom[$trid]['endy'] = $dom[$table_el]['rowspans'][$rsk]['endy'];
                    }
                    $rsk = 0;
                  } else {
                    ++$rsk;
                  }
                }
              }
              // add rowspan information to table element
              if ($rowspan > 1) {
                if (isset($this->footerlen[$this->page])) {
                  $this->footerpos[$this->page] = $this->pagelen[$this->page] - $this->footerlen[$this->page];
                } else {
                  $this->footerpos[$this->page] = $this->pagelen[$this->page];
                }
                $trintmrkpos = $this->footerpos[$this->page];
                $trsid = array_push($dom[$table_el]['rowspans'], array('trid' => $trid, 'rowspan' => $rowspan, 'mrowspan' => $rowspan, 'colspan' => $colspan, 'startpage' => $this->page, 'startx' => $this->x, 'starty' => $this->y, 'intmrkpos' => $trintmrkpos));
              }
              $cellid = array_push($dom[$trid]['cellpos'], array('startx' => $this->x));
              if ($rowspan > 1) {
                $dom[$trid]['cellpos'][($cellid - 1)]['rowspanid'] = ($trsid - 1);
              }
              // push background colors
              if (isset($dom[$parentid]['bgcolor']) AND ($dom[$parentid]['bgcolor'] !== false)) {
                $dom[$trid]['cellpos'][($cellid - 1)]['bgcolor'] = $dom[$parentid]['bgcolor'];
              }
              $prevLastH = $this->lasth;
              // ****** write the cell content ******
              $this->MultiCell($cellw, $cellh, $cell_content, false, $lalign, false, 2, '', '', true, 0, true);
              $this->lasth = $prevLastH;
              $this->cMargin = $oldmargin;
              $dom[$trid]['cellpos'][($cellid - 1)]['endx'] = $this->x;
              // update the end of row position
              if ($rowspan <= 1) {
                if (isset($dom[$trid]['endy'])) {
                  if ($this->page == $dom[$trid]['endpage']) {
                    $dom[$trid]['endy'] = max($this->y, $dom[$trid]['endy']);
                  } elseif ($this->page > $dom[$trid]['endpage']) {
                    $dom[$trid]['endy'] = $this->y;
                  }
                } else {
                  $dom[$trid]['endy'] = $this->y;
                }
                if (isset($dom[$trid]['endpage'])) {
                  $dom[$trid]['endpage'] = max($this->page, $dom[$trid]['endpage']);
                } else {
                  $dom[$trid]['endpage'] = $this->page;
                }
              } else {
                // account for row-spanned cells
                $dom[$table_el]['rowspans'][($trsid - 1)]['endx'] = $this->x;
                $dom[$table_el]['rowspans'][($trsid - 1)]['endy'] = $this->y;
                $dom[$table_el]['rowspans'][($trsid - 1)]['endpage'] = $this->page;
              }
              if (isset($dom[$table_el]['rowspans'])) {
                // update endy and endpage on rowspanned cells
                foreach ($dom[$table_el]['rowspans'] as $k => $trwsp) {
                  if ($trwsp['rowspan'] > 0) {
                    if (isset($dom[$trid]['endpage'])) {
                      if ($trwsp['endpage'] == $dom[$trid]['endpage']) {
                        $dom[$table_el]['rowspans'][$k]['endy'] = max($dom[$trid]['endy'], $trwsp['endy']);
                      } elseif ($trwsp['endpage'] < $dom[$trid]['endpage']) {
                        $dom[$table_el]['rowspans'][$k]['endy'] = $dom[$trid]['endy'];
                        $dom[$table_el]['rowspans'][$k]['endpage'] = $dom[$trid]['endpage'];
                      } else {
                        $dom[$trid]['endy'] = $this->pagedim[$dom[$trid]['endpage']]['hk'] - $this->pagedim[$dom[$trid]['endpage']]['bm'];
                      }
                    }
                  }
                }
              }
              $this->x += ($cellspacingx / 2);
            } else {
              // opening tag (or self-closing tag)
              if (!isset($opentagpos)) {
                if (!$this->InFooter) {
                  if (isset($this->footerlen[$this->page])) {
                    $this->footerpos[$this->page] = $this->pagelen[$this->page] - $this->footerlen[$this->page];
                  } else {
                    $this->footerpos[$this->page] = $this->pagelen[$this->page];
                  }
                  $opentagpos = $this->footerpos[$this->page];
                }
              }
              $this->openHTMLTagHandler($dom, $key, $cell);
            }
          } else {
            // closing tag
            $this->closeHTMLTagHandler($dom, $key, $cell);
          }
        } elseif (strlen($dom[$key]['value']) > 0) {
          // print list-item
          if (!$this->empty_string($this->lispacer)) {
            $this->SetFont($pfontname, $pfontstyle, $pfontsize);
            $this->lasth = $this->FontSize * $this->cell_height_ratio;
            $minstartliney = $this->y;
            $this->putHtmlListBullet($this->listnum, $this->lispacer, $pfontsize);
            $this->SetFont($curfontname, $curfontstyle, $curfontsize);
            $this->lasth = $this->FontSize * $this->cell_height_ratio;
            if (is_numeric($pfontsize) AND ($pfontsize > 0) AND is_numeric($curfontsize) AND ($curfontsize > 0) AND ($pfontsize != $curfontsize)) {
              $this->y += (($pfontsize - $curfontsize) / $this->k);
              $minstartliney = min($this->y, $minstartliney);
            }
          }
          // text
          $this->htmlvspace = 0;
          if ((!$this->premode) AND ($this->rtl OR $this->tmprtl)) {
            // reverse spaces order
            $len1 = strlen($dom[$key]['value']);
            $lsp = $len1 - strlen(ltrim($dom[$key]['value']));
            $rsp = $len1 - strlen(rtrim($dom[$key]['value']));
            $tmpstr = '';
            if ($rsp > 0) {
              $tmpstr .= substr($dom[$key]['value'], -$rsp);
            }
            $tmpstr .= trim($dom[$key]['value']);
            if ($lsp > 0) {
              $tmpstr .= substr($dom[$key]['value'], 0, $lsp);
            }
            $dom[$key]['value'] = $tmpstr;
          }
          if ($newline) {
            if (!$this->premode) {
              if (($this->rtl OR $this->tmprtl)) {
                $dom[$key]['value'] = rtrim($dom[$key]['value']);
              } else {
                $dom[$key]['value'] = ltrim($dom[$key]['value']);
              }
            }
            $newline = false;
            $firstblock = true;
          } else {
            $firstblock = false;
          }
          $strrest = '';
          if (!empty($this->HREF) AND (isset($this->HREF['url']))) {
            // HTML <a> Link
            $strrest = $this->addHtmlLink($this->HREF['url'], $dom[$key]['value'], $wfill, true, $this->HREF['color'], $this->HREF['style']);
          } else {
            $ctmpmargin = $this->cMargin;
            $this->cMargin = 0;
            // ****** write only until the end of the line and get the rest ******
            $strrest = $this->Write($this->lasth, $dom[$key]['value'], '', $wfill, '', false, 0, true, $firstblock);
            $this->cMargin = $ctmpmargin;
          }
          if (strlen($strrest) > 0) {
            // store the remaining string on the previous $key position
            $this->newline = true;
            if ($cell) {
              if ($this->rtl) {
                $this->x -= $this->cMargin;
              } else {
                $this->x += $this->cMargin;
              }
            }
            if ($strrest == $dom[$key]['value']) {
              // used to avoid infinite loop
              ++$loop;
            } else {
              $loop = 0;
            }
            $dom[$key]['value'] = ltrim($strrest);
            if ($loop < 3) {
              --$key;
            }
          } else {
            $loop = 0;
          }
        }
        ++$key;
      } // end for each $key
      // align the last line
      if (isset($startlinex)) {
        $yshift = $minstartliney - $startliney;
        if (($yshift > 0) OR ($this->page > $startlinepage)) {
          $yshift = 0;
        }
        if ((isset($plalign) AND ((($plalign == 'C') OR ($plalign == 'J') OR (($plalign == 'R') AND (!$this->rtl)) OR (($plalign == 'L') AND ($this->rtl))))) OR ($yshift < 0)) {
          // the last line must be shifted to be aligned as requested
          $linew = abs($this->endlinex - $startlinex);
          $pstart = substr($this->getPageBuffer($startlinepage), 0, $startlinepos);
          if (isset($opentagpos) AND isset($this->footerlen[$startlinepage]) AND (!$this->InFooter)) {
            $this->footerpos[$startlinepage] = $this->pagelen[$startlinepage] - $this->footerlen[$startlinepage];
            $midpos = min($opentagpos, $this->footerpos[$startlinepage]);
          } elseif (isset($opentagpos)) {
            $midpos = $opentagpos;
          } elseif (isset($this->footerlen[$startlinepage]) AND (!$this->InFooter)) {
            $this->footerpos[$startlinepage] = $this->pagelen[$startlinepage] - $this->footerlen[$startlinepage];
            $midpos = $this->footerpos[$startlinepage];
          } else {
            $midpos = 0;
          }
          if ($midpos > 0) {
            $pmid = substr($this->getPageBuffer($startlinepage), $startlinepos, ($midpos - $startlinepos));
            $pend = substr($this->getPageBuffer($startlinepage), $midpos);
          } else {
            $pmid = substr($this->getPageBuffer($startlinepage), $startlinepos);
            $pend = '';
          }
          // calculate shifting amount
          $tw = $w;
          if ($this->lMargin != $prevlMargin) {
            $tw += ($prevlMargin - $this->lMargin);
          }
          if ($this->rMargin != $prevrMargin) {
            $tw += ($prevrMargin - $this->rMargin);
          }
          $mdiff = abs($tw - $linew);
          if ($plalign == 'C') {
            if ($this->rtl) {
              $t_x = -($mdiff / 2);
            } else {
              $t_x = ($mdiff / 2);
            }
          } elseif (($plalign == 'R') AND (!$this->rtl)) {
            // right alignment on LTR document
            $t_x = $mdiff;
          } elseif (($plalign == 'L') AND ($this->rtl)) {
            // left alignment on RTL document
            $t_x = -$mdiff;
          } else {
            $t_x = 0;
          }
          if (($t_x != 0) OR ($yshift < 0)) {
            // shift the line
            $trx = sprintf('1 0 0 1 %.3F %.3F cm', ($t_x * $this->k), ($yshift * $this->k));
            $this->setPageBuffer($startlinepage, $pstart."\nq\n".$trx."\n".$pmid."\nQ\n".$pend);
            $endlinepos = strlen($pstart."\nq\n".$trx."\n".$pmid."\nQ\n");
            // shift the annotations and links
            if (isset($this->PageAnnots[$this->page])) {
              foreach ($this->PageAnnots[$this->page] as $pak => $pac) {
                if ($pak >= $pask) {
                  $this->PageAnnots[$this->page][$pak]['x'] += $t_x;
                  $this->PageAnnots[$this->page][$pak]['y'] -= $yshift;
                }
              }
            }
            $this->y -= $yshift;
          }
        }
      }
      if ($ln AND (!($cell AND ($dom[$key-1]['value'] == 'table')))) {
        $this->Ln($this->lasth);
      }
      // restore previous values
      $this->setGraphicVars($gvars);
      if ($this->page > $prevPage) {
        $this->lMargin = $this->pagedim[$this->page]['olm'];
        $this->rMargin = $this->pagedim[$this->page]['orm'];
      }
      unset($dom);
    }

    /**
     * Process opening tags.
     * @param array $dom html dom array
     * @param int $key current element id
     * @param boolean $cell if true add the default cMargin space to each new line (default false).
     * @access protected
     */
    protected function openHTMLTagHandler(&$dom, $key, $cell=false) {
      $params = null;
      $tag = $dom[$key];
      $parent = $dom[($dom[$key]['parent'])];
      $firstorlast = ($key == 1);
      // check for text direction attribute
      if (isset($tag['attribute']['dir'])) {
        $this->tmprtl = $tag['attribute']['dir'] == 'rtl' ? 'R' : 'L';
      } else {
        $this->tmprtl = false;
      }
      //Opening tag
      switch($tag['value']) {
        case 'table': {
          $cp = 0;
          $cs = 0;
          $dom[$key]['rowspans'] = array();
          if (!$this->empty_string($dom[$key]['thead'])) {
            // set table header
            $this->thead = $dom[$key]['thead'];
          }
          if (isset($tag['attribute']['cellpadding'])) {
            $cp = $this->getHTMLUnitToUnits($tag['attribute']['cellpadding'], 1, 'px');
            $this->oldcMargin = $this->cMargin;
            $this->cMargin = $cp;
          }
          if (isset($tag['attribute']['cellspacing'])) {
            $cs = $this->getHTMLUnitToUnits($tag['attribute']['cellspacing'], 1, 'px');
          }
          $this->checkPageBreak((2 * $cp) + (2 * $cs) + $this->lasth);
          break;
        }
        case 'tr': {
          // array of columns positions
          $dom[$key]['cellpos'] = array();
          break;
        }
        case 'hr': {
          $this->addHTMLVertSpace(1, $cell, '', $firstorlast, $tag['value'], false);
          $this->htmlvspace = 0;
          $wtmp = $this->w - $this->lMargin - $this->rMargin;
          if ((isset($tag['attribute']['width'])) AND ($tag['attribute']['width'] != '')) {
            $hrWidth = $this->getHTMLUnitToUnits($tag['attribute']['width'], $wtmp, 'px');
          } else {
            $hrWidth = $wtmp;
          }
          $x = $this->GetX();
          $y = $this->GetY();
          $prevlinewidth = $this->GetLineWidth();
          $this->Line($x, $y, $x + $hrWidth, $y);
          $this->SetLineWidth($prevlinewidth);
          $this->addHTMLVertSpace(1, $cell, '', !isset($dom[($key + 1)]), $tag['value'], false);
          break;
        }
        case 'a': {
          if (array_key_exists('href', $tag['attribute'])) {
            $this->HREF['url'] = $tag['attribute']['href'];
          }
          $this->HREF['color'] = $this->htmlLinkColorArray;
          $this->HREF['style'] = $this->htmlLinkFontStyle;
          if (array_key_exists('style', $tag['attribute'])) {
            // get style attributes
            preg_match_all('/([^;:\s]*):([^;]*)/', $tag['attribute']['style'], $style_array, PREG_PATTERN_ORDER);
            $astyle = array();
            while (list($id, $name) = each($style_array[1])) {
              $name = strtolower($name);
              $astyle[$name] = trim($style_array[2][$id]);
            }
            if (isset($astyle['color'])) {
              $this->HREF['color'] = $this->convertHTMLColorToDec($astyle['color']);
            }
            if (isset($astyle['text-decoration'])) {
              $this->HREF['style'] = '';
              $decors = explode(' ', strtolower($astyle['text-decoration']));
              foreach ($decors as $dec) {
                $dec = trim($dec);
                if (!$this->empty_string($dec)) {
                  if ($dec{0} == 'u') {
                    $this->HREF['style'] .= 'U';
                  } elseif ($dec{0} == 'l') {
                    $this->HREF['style'] .= 'D';
                  }
                }
              }
            }
          }
          break;
        }
        case 'img': {
          if (isset($tag['attribute']['src'])) {
            // replace relative path with real server path
            if ($tag['attribute']['src'][0] == '/') {
              $tag['attribute']['src'] = $_SERVER['DOCUMENT_ROOT'].$tag['attribute']['src'];
            }
            $tag['attribute']['src'] = urldecode($tag['attribute']['src']);
            $tag['attribute']['src'] = str_replace(K_PATH_URL, K_PATH_MAIN, $tag['attribute']['src']);
            if (!isset($tag['attribute']['width'])) {
              $tag['attribute']['width'] = 0;
            }
            if (!isset($tag['attribute']['height'])) {
              $tag['attribute']['height'] = 0;
            }
            //if (!isset($tag['attribute']['align'])) {
              // the only alignment supported is "bottom"
              // further development is required for other modes.
              $tag['attribute']['align'] = 'bottom';
            //}
            switch($tag['attribute']['align']) {
              case 'top': {
                $align = 'T';
                break;
              }
              case 'middle': {
                $align = 'M';
                break;
              }
              case 'bottom': {
                $align = 'B';
                break;
              }
              default: {
                $align = 'B';
                break;
              }
            }
            $fileinfo = pathinfo($tag['attribute']['src']);
            if (isset($fileinfo['extension']) AND (!$this->empty_string($fileinfo['extension']))) {
              $type = strtolower($fileinfo['extension']);
            }
            $prevy = $this->y;
            $xpos = $this->GetX();
            if (isset($dom[($key - 1)]) AND ($dom[($key - 1)]['value'] == ' ')) {
              if ($this->rtl) {
                $xpos += $this->GetStringWidth(' ');
              } else {
                $xpos -= $this->GetStringWidth(' ');
              }
            }
            $imglink = '';
            if (isset($this->HREF['url']) AND !$this->empty_string($this->HREF['url'])) {
              $imglink = $this->HREF['url'];
              if ($imglink{0} == '#') {
                // convert url to internal link
                $page = intval(substr($imglink, 1));
                $imglink = $this->AddLink();
                $this->SetLink($imglink, 0, $page);
              }
            }
            $border = 0;
            if (isset($tag['attribute']['border']) AND !empty($tag['attribute']['border'])) {
              // currently only support 1 (frame) or a combination of 'LTRB'
              $border = $tag['attribute']['border'];
            }
            if (isset($tag['attribute']['width'])) {
              $iw = $this->getHTMLUnitToUnits($tag['attribute']['width'], 1, 'px', false);
            }
            if (isset($tag['attribute']['height'])) {
              $ih = $this->getHTMLUnitToUnits($tag['attribute']['height'], 1, 'px', false);
            }
            if (($type == 'eps') OR ($type == 'ai')) {
              $this->ImageEps($tag['attribute']['src'], $xpos, $this->GetY(), $iw, $ih, $imglink, true, $align, '', $border);
            } else {
              $this->Image($tag['attribute']['src'], $xpos, $this->GetY(), $iw, $ih, '', $imglink, $align, false, 300, '', false, false, $border);
            }
            switch($align) {
              case 'T': {
                $this->y = $prevy;
                break;
              }
              case 'M': {
                $this->y = (($this->img_rb_y + $prevy - ($tag['fontsize'] / $this->k)) / 2) ;
                break;
              }
              case 'B': {
                $this->y = $this->img_rb_y - ($tag['fontsize'] / $this->k);
                break;
              }
            }
          }
          break;
        }
        case 'dl': {
          ++$this->listnum;
          $this->addHTMLVertSpace(0, $cell, '', $firstorlast, $tag['value'], false);
          break;
        }
        case 'dt': {
          $this->addHTMLVertSpace(1, $cell, '', $firstorlast, $tag['value'], false);
          break;
        }
        case 'dd': {
          if ($this->rtl) {
            $this->rMargin += $this->listindent;
          } else {
            $this->lMargin += $this->listindent;
          }
          $this->addHTMLVertSpace(1, $cell, '', $firstorlast, $tag['value'], false);
          break;
        }
        case 'ul':
        case 'ol': {
          $this->addHTMLVertSpace(0, $cell, '', $firstorlast, $tag['value'], false);
          $this->htmlvspace = 0;
          ++$this->listnum;
          if ($tag['value'] == 'ol') {
            $this->listordered[$this->listnum] = true;
          } else {
            $this->listordered[$this->listnum] = false;
          }
          if (isset($tag['attribute']['start'])) {
            $this->listcount[$this->listnum] = intval($tag['attribute']['start']) - 1;
          } else {
            $this->listcount[$this->listnum] = 0;
          }
          if ($this->rtl) {
            $this->rMargin += $this->listindent;
          } else {
            $this->lMargin += $this->listindent;
          }
          $this->addHTMLVertSpace(0, $cell, '', $firstorlast, $tag['value'], false);
          $this->htmlvspace = 0;
          break;
        }
        case 'li': {
          $this->addHTMLVertSpace(1, $cell, '', $firstorlast, $tag['value'], false);
          if ($this->listordered[$this->listnum]) {
            // ordered item
            if (isset($parent['attribute']['type']) AND !$this->empty_string($parent['attribute']['type'])) {
              $this->lispacer = $parent['attribute']['type'];
            } elseif (isset($parent['listtype']) AND !$this->empty_string($parent['listtype'])) {
              $this->lispacer = $parent['listtype'];
            } elseif (isset($this->lisymbol) AND !$this->empty_string($this->lisymbol)) {
              $this->lispacer = $this->lisymbol;
            } else {
              $this->lispacer = '#';
            }
            ++$this->listcount[$this->listnum];
            if (isset($tag['attribute']['value'])) {
              $this->listcount[$this->listnum] = intval($tag['attribute']['value']);
            }
          } else {
            // unordered item
            if (isset($parent['attribute']['type']) AND !$this->empty_string($parent['attribute']['type'])) {
              $this->lispacer = $parent['attribute']['type'];
            } elseif (isset($parent['listtype']) AND !$this->empty_string($parent['listtype'])) {
              $this->lispacer = $parent['listtype'];
            } elseif (isset($this->lisymbol) AND !$this->empty_string($this->lisymbol)) {
              $this->lispacer = $this->lisymbol;
            } else {
              $this->lispacer = '!';
            }
          }
          break;
        }
        case 'blockquote': {
          if ($this->rtl) {
            $this->rMargin += $this->listindent;
          } else {
            $this->lMargin += $this->listindent;
          }
          $this->addHTMLVertSpace(2, $cell, '', $firstorlast, $tag['value'], false);
          break;
        }
        case 'br': {
          $this->Ln('', $cell);
          break;
        }
        case 'div': {
          $this->addHTMLVertSpace(1, $cell, '', $firstorlast, $tag['value'], false);
          break;
        }
        case 'p': {
          $this->addHTMLVertSpace(2, $cell, '', $firstorlast, $tag['value'], false);
          break;
        }
        case 'pre': {
          $this->addHTMLVertSpace(1, $cell, '', $firstorlast, $tag['value'], false);
          $this->premode = true;
          break;
        }
        case 'sup': {
          $this->SetXY($this->GetX(), $this->GetY() - ((0.7 * $this->FontSizePt) / $this->k));
          break;
        }
        case 'sub': {
          $this->SetXY($this->GetX(), $this->GetY() + ((0.3 * $this->FontSizePt) / $this->k));
          break;
        }
        case 'h1':
        case 'h2':
        case 'h3':
        case 'h4':
        case 'h5':
        case 'h6': {
          $this->addHTMLVertSpace(1, $cell, ($tag['fontsize'] * 1.5) / $this->k, $firstorlast, $tag['value'], false);
          break;
        }
        case 'tcpdf': {
          // NOT HTML: used to call TCPDF methods
          if (isset($tag['attribute']['method'])) {
            $tcpdf_method = $tag['attribute']['method'];
            if (method_exists($this, $tcpdf_method)) {
              if (isset($tag['attribute']['params']) AND (!empty($tag['attribute']['params']))) {
                eval('$params = array('.$tag['attribute']['params'].');');
                call_user_func_array(array($this, $tcpdf_method), $params);
              } else {
                $this->$tcpdf_method();
              }
              $this->newline = true;
            }
          }
        }
        default: {
          break;
        }
      }
    }

    /**
     * Process closing tags.
     * @param array $dom html dom array
     * @param int $key current element id
     * @param boolean $cell if true add the default cMargin space to each new line (default false).
     * @access protected
     */
    protected function closeHTMLTagHandler(&$dom, $key, $cell=false) {
      $tag = $dom[$key];
      $parent = $dom[($dom[$key]['parent'])];
      $firstorlast = ((!isset($dom[($key + 1)])) OR ((!isset($dom[($key + 2)])) AND ($dom[($key + 1)]['value'] == 'marker')));
      //Closing tag
      switch($tag['value']) {
        case 'tr': {
          $table_el = $dom[($dom[$key]['parent'])]['parent'];
          if(!isset($parent['endy'])) {
            $dom[($dom[$key]['parent'])]['endy'] = $this->y;
            $parent['endy'] = $this->y;
          }
          if(!isset($parent['endpage'])) {
            $dom[($dom[$key]['parent'])]['endpage'] = $this->page;
            $parent['endpage'] = $this->page;
          }
          // update row-spanned cells
          if (isset($dom[$table_el]['rowspans'])) {
            foreach ($dom[$table_el]['rowspans'] as $k => $trwsp) {
              $dom[$table_el]['rowspans'][$k]['rowspan'] -= 1;
              if ($dom[$table_el]['rowspans'][$k]['rowspan'] == 0) {
                if ($dom[$table_el]['rowspans'][$k]['endpage'] == $parent['endpage']) {
                  $dom[($dom[$key]['parent'])]['endy'] = max($dom[$table_el]['rowspans'][$k]['endy'], $parent['endy']);
                } elseif ($dom[$table_el]['rowspans'][$k]['endpage'] > $parent['endpage']) {
                  $dom[($dom[$key]['parent'])]['endy'] = $dom[$table_el]['rowspans'][$k]['endy'];
                  $dom[($dom[$key]['parent'])]['endpage'] = $dom[$table_el]['rowspans'][$k]['endpage'];
                }
              }
            }
            // report new endy and endpage to the rowspanned cells
            foreach ($dom[$table_el]['rowspans'] as $k => $trwsp) {
              if ($dom[$table_el]['rowspans'][$k]['rowspan'] == 0) {
                $dom[$table_el]['rowspans'][$k]['endpage'] = max($dom[$table_el]['rowspans'][$k]['endpage'], $dom[($dom[$key]['parent'])]['endpage']);
                $dom[($dom[$key]['parent'])]['endpage'] = $dom[$table_el]['rowspans'][$k]['endpage'];
                $dom[$table_el]['rowspans'][$k]['endy'] = max($dom[$table_el]['rowspans'][$k]['endy'], $dom[($dom[$key]['parent'])]['endy']);
                $dom[($dom[$key]['parent'])]['endy'] = $dom[$table_el]['rowspans'][$k]['endy'];
              }
            }
            // update remaining rowspanned cells
            foreach ($dom[$table_el]['rowspans'] as $k => $trwsp) {
              if ($dom[$table_el]['rowspans'][$k]['rowspan'] == 0) {
                $dom[$table_el]['rowspans'][$k]['endpage'] = $dom[($dom[$key]['parent'])]['endpage'];
                $dom[$table_el]['rowspans'][$k]['endy'] = $dom[($dom[$key]['parent'])]['endy'];
              }
            }
          }
          $this->setPage($dom[($dom[$key]['parent'])]['endpage']);
          $this->y = $dom[($dom[$key]['parent'])]['endy'];
          if (isset($dom[$table_el]['attribute']['cellspacing'])) {
            $cellspacing = $this->getHTMLUnitToUnits($dom[$table_el]['attribute']['cellspacing'], 1, 'px');
            $this->y += $cellspacing;
          }
          $this->Ln(0, $cell);
          $this->x = $parent['startx'];
          // account for booklet mode
          if ($this->page > $parent['startpage']) {
            if (($this->rtl) AND ($this->pagedim[$this->page]['orm'] != $this->pagedim[$parent['startpage']]['orm'])) {
              $this->x += ($this->pagedim[$this->page]['orm'] - $this->pagedim[$parent['startpage']]['orm']);
            } elseif ((!$this->rtl) AND ($this->pagedim[$this->page]['olm'] != $this->pagedim[$parent['startpage']]['olm'])) {
              $this->x += ($this->pagedim[$this->page]['olm'] - $this->pagedim[$parent['startpage']]['olm']);
            }
          }
          break;
        }
        case 'table': {
          // draw borders
          $table_el = $parent;
          if ((isset($table_el['attribute']['border']) AND ($table_el['attribute']['border'] > 0))
            OR (isset($table_el['style']['border']) AND ($table_el['style']['border'] > 0))) {
              $border = 1;
          } else {
            $border = 0;
          }
          // fix bottom line alignment of last line before page break
          foreach ($dom[($dom[$key]['parent'])]['trids'] as $j => $trkey) {
            // update row-spanned cells
            if (isset($dom[($dom[$key]['parent'])]['rowspans'])) {
              foreach ($dom[($dom[$key]['parent'])]['rowspans'] as $k => $trwsp) {
                if ($trwsp['trid'] == $trkey) {
                  $dom[($dom[$key]['parent'])]['rowspans'][$k]['mrowspan'] -= 1;
                }
                if (isset($prevtrkey) AND ($trwsp['trid'] == $prevtrkey) AND ($trwsp['mrowspan'] >= 0)) {
                  $dom[($dom[$key]['parent'])]['rowspans'][$k]['trid'] = $trkey;
                }
              }
            }
            if (isset($prevtrkey) AND ($dom[$trkey]['startpage'] > $dom[$prevtrkey]['endpage'])) {
              $pgendy = $this->pagedim[$dom[$prevtrkey]['endpage']]['hk'] - $this->pagedim[$dom[$prevtrkey]['endpage']]['bm'];
              $dom[$prevtrkey]['endy'] = $pgendy;
              // update row-spanned cells
              if (isset($dom[($dom[$key]['parent'])]['rowspans'])) {
                foreach ($dom[($dom[$key]['parent'])]['rowspans'] as $k => $trwsp) {
                  if (($trwsp['trid'] == $trkey) AND ($trwsp['mrowspan'] == 1) AND ($trwsp['endpage'] == $dom[$prevtrkey]['endpage'])) {
                    $dom[($dom[$key]['parent'])]['rowspans'][$k]['endy'] = $pgendy;
                    $dom[($dom[$key]['parent'])]['rowspans'][$k]['mrowspan'] = -1;
                  }
                }
              }
            }
            $prevtrkey = $trkey;
            $table_el = $dom[($dom[$key]['parent'])];
          }
          // for each row
          foreach ($table_el['trids'] as $j => $trkey) {
            $parent = $dom[$trkey];
            // for each cell on the row
            foreach ($parent['cellpos'] as $k => $cellpos) {
              if (isset($cellpos['rowspanid']) AND ($cellpos['rowspanid'] >= 0)) {
                $cellpos['startx'] = $table_el['rowspans'][($cellpos['rowspanid'])]['startx'];
                $cellpos['endx'] = $table_el['rowspans'][($cellpos['rowspanid'])]['endx'];
                $endy = $table_el['rowspans'][($cellpos['rowspanid'])]['endy'];
                $startpage = $table_el['rowspans'][($cellpos['rowspanid'])]['startpage'];
                $endpage = $table_el['rowspans'][($cellpos['rowspanid'])]['endpage'];
              } else {
                $endy = $parent['endy'];
                $startpage = $parent['startpage'];
                $endpage = $parent['endpage'];
              }
              if ($endpage > $startpage) {
                // design borders around HTML cells.
                for ($page=$startpage; $page <= $endpage; ++$page) {
                  $this->setPage($page);
                  if ($page == $startpage) {
                    $this->y = $parent['starty']; // put cursor at the beginning of row on the first page
                    $ch = $this->getPageHeight() - $parent['starty'] - $this->getBreakMargin();
                    $cborder = $this->getBorderMode($border, $position='start');
                  } elseif ($page == $endpage) {
                    $this->y = $this->tMargin; // put cursor at the beginning of last page
                    $ch = $endy - $this->tMargin;
                    $cborder = $this->getBorderMode($border, $position='end');
                  } else {
                    $this->y = $this->tMargin; // put cursor at the beginning of the current page
                    $ch = $this->getPageHeight() - $this->tMargin - $this->getBreakMargin();
                    $cborder = $this->getBorderMode($border, $position='middle');
                  }
                  if (isset($cellpos['bgcolor']) AND ($cellpos['bgcolor']) !== false) {
                    $this->SetFillColorArray($cellpos['bgcolor']);
                    $fill = true;
                  } else {
                    $fill = false;
                  }
                  $cw = abs($cellpos['endx'] - $cellpos['startx']);
                  $this->x = $cellpos['startx'];
                  // account for margin changes
                  if ($page > $startpage) {
                    if (($this->rtl) AND ($this->pagedim[$page]['orm'] != $this->pagedim[$startpage]['orm'])) {
                      $this->x -= ($this->pagedim[$page]['orm'] - $this->pagedim[$startpage]['orm']);
                    } elseif ((!$this->rtl) AND ($this->pagedim[$page]['lm'] != $this->pagedim[$startpage]['olm'])) {
                      $this->x += ($this->pagedim[$page]['olm'] - $this->pagedim[$startpage]['olm']);
                    }
                  }
                  // design a cell around the text
                  $ccode = $this->FillColor."\n".$this->getCellCode($cw, $ch, '', $cborder, 1, '', $fill, '', 0, true);
                  if ($cborder OR $fill) {
                    $pagebuff = $this->getPageBuffer($this->page);
                    $pstart = substr($pagebuff, 0, $this->intmrk[$this->page]);
                    $pend = substr($pagebuff, $this->intmrk[$this->page]);
                    $this->setPageBuffer($this->page, $pstart.$ccode."\n".$pend);
                    $this->intmrk[$this->page] += strlen($ccode."\n");
                  }
                }
              } else {
                $this->setPage($startpage);
                if (isset($cellpos['bgcolor']) AND ($cellpos['bgcolor']) !== false) {
                  $this->SetFillColorArray($cellpos['bgcolor']);
                  $fill = true;
                } else {
                  $fill = false;
                }
                $this->x = $cellpos['startx'];
                $this->y = $parent['starty'];
                $cw = abs($cellpos['endx'] - $cellpos['startx']);
                $ch = $endy - $parent['starty'];
                // design a cell around the text
                $ccode = $this->FillColor."\n".$this->getCellCode($cw, $ch, '', $border, 1, '', $fill, '', 0, true);
                if ($border OR $fill) {
                  if (end($this->transfmrk[$this->page]) !== false) {
                    $pagemarkkey = key($this->transfmrk[$this->page]);
                    $pagemark = &$this->transfmrk[$this->page][$pagemarkkey];
                  } elseif ($this->InFooter) {
                    $pagemark = &$this->footerpos[$this->page];
                  } else {
                    $pagemark = &$this->intmrk[$this->page];
                  }
                  $pagebuff = $this->getPageBuffer($this->page);
                  $pstart = substr($pagebuff, 0, $pagemark);
                  $pend = substr($pagebuff, $pagemark);
                  $this->setPageBuffer($this->page, $pstart.$ccode."\n".$pend);
                  $pagemark += strlen($ccode."\n");
                }
              }
            }
            if (isset($table_el['attribute']['cellspacing'])) {
              $cellspacing = $this->getHTMLUnitToUnits($table_el['attribute']['cellspacing'], 1, 'px');
              $this->y += $cellspacing;
            }
            $this->Ln(0, $cell);
            $this->x = $parent['startx'];
            if ($endpage > $startpage) {
              if (($this->rtl) AND ($this->pagedim[$endpage]['orm'] != $this->pagedim[$startpage]['orm'])) {
                $this->x += ($this->pagedim[$endpage]['orm'] - $this->pagedim[$startpage]['orm']);
              } elseif ((!$this->rtl) AND ($this->pagedim[$endpage]['olm'] != $this->pagedim[$startpage]['olm'])) {
                $this->x += ($this->pagedim[$endpage]['olm'] - $this->pagedim[$startpage]['olm']);
              }
            }
          }
          if (isset($parent['cellpadding'])) {
            $this->cMargin = $this->oldcMargin;
          }
          $this->lasth = $this->FontSize * $this->cell_height_ratio;
          if (!$this->empty_string($this->theadMargin)) {
            // restore top margin
            $this->tMargin = $this->theadMargin;
            $this->pagedim[$this->page]['tm'] = $this->theadMargin;
          }
          // reset table header
          $this->thead = '';
          $this->theadMargin = '';
          break;
        }
        case 'a': {
          $this->HREF = '';
          break;
        }
        case 'sup': {
          $this->SetXY($this->GetX(), $this->GetY() + ((0.7 * $parent['fontsize']) / $this->k));
          break;
        }
        case 'sub': {
          $this->SetXY($this->GetX(), $this->GetY() - ((0.3 * $parent['fontsize'])/$this->k));
          break;
        }
        case 'div': {
          $this->addHTMLVertSpace(1, $cell, '', $firstorlast, $tag['value'], true);
          break;
        }
        case 'blockquote': {
          if ($this->rtl) {
            $this->rMargin -= $this->listindent;
          } else {
            $this->lMargin -= $this->listindent;
          }
          $this->addHTMLVertSpace(2, $cell, '', $firstorlast, $tag['value'], true);
          break;
        }
        case 'p': {
          $this->addHTMLVertSpace(2, $cell, '', $firstorlast, $tag['value'], true);
          break;
        }
        case 'pre': {
          $this->addHTMLVertSpace(1, $cell, '', $firstorlast, $tag['value'], true);
          $this->premode = false;
          break;
        }
        case 'dl': {
          --$this->listnum;
          if ($this->listnum <= 0) {
            $this->listnum = 0;
            $this->addHTMLVertSpace(2, $cell, '', $firstorlast, $tag['value'], true);
          }
          break;
        }
        case 'dt': {
          $this->lispacer = '';
          $this->addHTMLVertSpace(0, $cell, '', $firstorlast, $tag['value'], true);
          break;
        }
        case 'dd': {
          $this->lispacer = '';
          if ($this->rtl) {
            $this->rMargin -= $this->listindent;
          } else {
            $this->lMargin -= $this->listindent;
          }
          $this->addHTMLVertSpace(0, $cell, '', $firstorlast, $tag['value'], true);
          break;
        }
        case 'ul':
        case 'ol': {
          --$this->listnum;
          $this->lispacer = '';
          if ($this->rtl) {
            $this->rMargin -= $this->listindent;
          } else {
            $this->lMargin -= $this->listindent;
          }
          if ($this->listnum <= 0) {
            $this->listnum = 0;
            $this->addHTMLVertSpace(2, $cell, '', $firstorlast, $tag['value'], true);
          }
          $this->lasth = $this->FontSize * $this->cell_height_ratio;
          break;
        }
        case 'li': {
          $this->lispacer = '';
          $this->addHTMLVertSpace(0, $cell, '', $firstorlast, $tag['value'], true);
          break;
        }
        case 'h1':
        case 'h2':
        case 'h3':
        case 'h4':
        case 'h5':
        case 'h6': {
          $this->addHTMLVertSpace(1, $cell, ($parent['fontsize'] * 1.5) / $this->k, $firstorlast, $tag['value'], true);
          break;
        }
        default : {
          break;
        }
      }
      $this->tmprtl = false;
    }

    /**
     * Add vertical spaces if needed.
     * @param int $n number of spaces to add
     * @param boolean $cell if true add the default cMargin space to each new line (default false).
     * @param string $h The height of the break. By default, the value equals the height of the last printed cell.
     * @param boolean $firstorlast if true do not print additional empty lines.
     * @param string $tag HTML tag to which this space will be applied
     * @param boolean $closing true if this space will be applied to a closing tag, false otherwise
     * @access protected
     */
    protected function addHTMLVertSpace($n, $cell=false, $h='', $firstorlast=false, $tag='', $closing=false) {
      if ($firstorlast) {
        $this->Ln(0, $cell);
        $this->htmlvspace = 0;
        return;
      }
      if (isset($this->tagvspaces[$tag][intval($closing)]['n'])) {
        $n = $this->tagvspaces[$tag][intval($closing)]['n'];
      }
      if (isset($this->tagvspaces[$tag][intval($closing)]['h'])) {
        $h = $this->tagvspaces[$tag][intval($closing)]['h'];
      }
      if (is_string($h)) {
        $vsize = $n * $this->lasth;
      } else {
        $vsize = $n * $h;
      }
      if ($vsize > $this->htmlvspace) {
        $this->Ln(($vsize - $this->htmlvspace), $cell);
        $this->htmlvspace = $vsize;
      }
    }

    /**
     * Prints a cell (rectangular area) with optional borders, background color and html text string.
     * The upper-left corner of the cell corresponds to the current position. After the call, the current position moves to the right or to the next line.<br />
     * If automatic page breaking is enabled and the cell goes beyond the limit, a page break is done before outputting.
     * @param float $w Cell width. If 0, the cell extends up to the right margin.
     * @param float $h Cell minimum height. The cell extends automatically if needed.
     * @param float $x upper-left corner X coordinate
     * @param float $y upper-left corner Y coordinate
     * @param string $html html text to print. Default value: empty string.
     * @param mixed $border Indicates if borders must be drawn around the cell. The value can be either a number:<ul><li>0: no border (default)</li><li>1: frame</li></ul>or a string containing some or all of the following characters (in any order):<ul><li>L: left</li><li>T: top</li><li>R: right</li><li>B: bottom</li></ul>
     * @param int $ln Indicates where the current position should go after the call. Possible values are:<ul><li>0: to the right (or left for RTL language)</li><li>1: to the beginning of the next line</li><li>2: below</li></ul>
  Putting 1 is equivalent to putting 0 and calling Ln() just after. Default value: 0.
     * @param int $fill Indicates if the cell background must be painted (1) or transparent (0). Default value: 0.
     * @param boolean $reseth if true reset the last cell height (default true).
     * @param string $align Allows to center or align the text. Possible values are:<ul><li>L : left align</li><li>C : center</li><li>R : right align</li><li>'' : empty string : left for LTR or right for RTL</li></ul>
     * @param boolean $autopadding if true, uses internal padding and automatically adjust it to account for line width.
     * @access public
     * @uses MultiCell()
     * @see Multicell(), writeHTML()
     */
    public function writeHTMLCell($w, $h, $x, $y, $html='', $border=0, $ln=0, $fill=0, $reseth=true, $align='', $autopadding=true) {
      return $this->MultiCell($w, $h, $html, $border, $align, $fill, $ln, $x, $y, $reseth, 0, true, $autopadding, 0);
    }

    /**
     * Returns the HTML DOM array.
     * <ul><li>$dom[$key]['tag'] = true if tag, false otherwise;</li><li>$dom[$key]['value'] = tag name or text;</li><li>$dom[$key]['opening'] = true if opening tag, false otherwise;</li><li>$dom[$key]['attribute'] = array of attributes (attribute name is the key);</li><li>$dom[$key]['style'] = array of style attributes (attribute name is the key);</li><li>$dom[$key]['parent'] = id of parent element;</li><li>$dom[$key]['fontname'] = font family name;</li><li>$dom[$key]['fontstyle'] = font style;</li><li>$dom[$key]['fontsize'] = font size in points;</li><li>$dom[$key]['bgcolor'] = RGB array of background color;</li><li>$dom[$key]['fgcolor'] = RGB array of foreground color;</li><li>$dom[$key]['width'] = width in pixels;</li><li>$dom[$key]['height'] = height in pixels;</li><li>$dom[$key]['align'] = text alignment;</li><li>$dom[$key]['cols'] = number of colums in table;</li><li>$dom[$key]['rows'] = number of rows in table;</li></ul>
     * @param string $html html code
     * @return array
     * @access protected
     * @since 3.2.000 (2008-06-20)
     */
    protected function getHtmlDomArray($html) {
      // remove all unsupported tags (the line below lists all supported tags)
      $html = strip_tags($html, '<marker/><a><b><blockquote><br><br/><dd><del><div><dl><dt><em><font><h1><h2><h3><h4><h5><h6><hr><i><img><li><ol><p><pre><small><span><strong><sub><sup><table><tcpdf><td><th><thead><tr><tt><u><ul>');
      //replace some blank characters
      $html = preg_replace('/<pre/', '<xre', $html); // preserve pre tag
      $html = preg_replace('/<(table|tr|td|th|blockquote|dd|div|dt|h1|h2|h3|h4|h5|h6|br|hr|li|ol|ul|p)([^\>]*)>[\n\r\t]+/', '<\\1\\2>', $html);
      $html = preg_replace('@(\r\n|\r)@', "\n", $html);
      $repTable = array("\t" => ' ', "\0" => ' ', "\x0B" => ' ', "\\" => "\\\\");
      $html = strtr($html, $repTable);
      while (preg_match("'<xre([^\>]*)>(.*?)\n(.*?)</pre>'si", $html)) {
        // preserve newlines on <pre> tag
        $html = preg_replace("'<xre([^\>]*)>(.*?)\n(.*?)</pre>'si", "<xre\\1>\\2<br />\\3</pre>", $html);
      }
      $html = str_replace("\n", ' ', $html);
      // remove extra spaces from code
      $html = preg_replace('/[\s]+<\/(table|tr|td|th|ul|ol|li)>/', '</\\1>', $html);
      $html = preg_replace('/[\s]+<(tr|td|th|ul|ol|li|br)/', '<\\1', $html);
      $html = preg_replace('/<\/(table|tr|td|th|blockquote|dd|div|dt|h1|h2|h3|h4|h5|h6|hr|li|ol|ul|p)>[\s]+</', '</\\1><', $html);
      $html = preg_replace('/<\/(td|th)>/', '<marker style="font-size:0"/></\\1>', $html);
      $html = preg_replace('/<\/table>([\s]*)<marker style="font-size:0"\/>/', '</table>', $html);
      $html = preg_replace('/<img/', ' <img', $html);
      $html = preg_replace('/<img([^\>]*)>/xi', '<img\\1><span></span>', $html);
      $html = preg_replace('/<xre/', '<pre', $html); // restore pre tag
      // trim string
      $html = preg_replace('/^[\s]+/', '', $html);
      $html = preg_replace('/[\s]+$/', '', $html);
      // pattern for generic tag
      $tagpattern = '/(<[^>]+>)/';
      // explodes the string
      $a = preg_split($tagpattern, $html, -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY);
      // count elements
      $maxel = count($a);
      $elkey = 0;
      $key = 0;
      // create an array of elements
      $dom = array();
      $dom[$key] = array();
      // set first void element
      $dom[$key]['tag'] = false;
      $dom[$key]['value'] = '';
      $dom[$key]['parent'] = 0;
      $dom[$key]['fontname'] = $this->FontFamily;
      $dom[$key]['fontstyle'] = $this->FontStyle;
      $dom[$key]['fontsize'] = $this->FontSizePt;
      $dom[$key]['bgcolor'] = false;
      $dom[$key]['fgcolor'] = $this->fgcolor;
      $dom[$key]['align'] = '';
      $dom[$key]['listtype'] = '';
      $thead = false; // true when we are inside the THEAD tag
      ++$key;
      $level = array();
      array_push($level, 0); // root
      while ($elkey < $maxel) {
        $dom[$key] = array();
        $element = $a[$elkey];
        $dom[$key]['elkey'] = $elkey;
        if (preg_match($tagpattern, $element)) {
          // html tag
          $element = substr($element, 1, -1);
          // get tag name
          preg_match('/[\/]?([a-zA-Z0-9]*)/', $element, $tag);
          $tagname = strtolower($tag[1]);
          // check if we are inside a table header
          if ($tagname == 'thead') {
            if ($element{0} == '/') {
              $thead = false;
            } else {
              $thead = true;
            }
            ++$elkey;
            continue;
          }
          $dom[$key]['tag'] = true;
          $dom[$key]['value'] = $tagname;
          if ($element{0} == '/') {
            // closing html tag
            $dom[$key]['opening'] = false;
            $dom[$key]['parent'] = end($level);
            array_pop($level);
            $dom[$key]['fontname'] = $dom[($dom[($dom[$key]['parent'])]['parent'])]['fontname'];
            $dom[$key]['fontstyle'] = $dom[($dom[($dom[$key]['parent'])]['parent'])]['fontstyle'];
            $dom[$key]['fontsize'] = $dom[($dom[($dom[$key]['parent'])]['parent'])]['fontsize'];
            $dom[$key]['bgcolor'] = $dom[($dom[($dom[$key]['parent'])]['parent'])]['bgcolor'];
            $dom[$key]['fgcolor'] = $dom[($dom[($dom[$key]['parent'])]['parent'])]['fgcolor'];
            $dom[$key]['align'] = $dom[($dom[($dom[$key]['parent'])]['parent'])]['align'];
            if (isset($dom[($dom[($dom[$key]['parent'])]['parent'])]['listtype'])) {
              $dom[$key]['listtype'] = $dom[($dom[($dom[$key]['parent'])]['parent'])]['listtype'];
            }
            // set the number of columns in table tag
            if (($dom[$key]['value'] == 'tr') AND (!isset($dom[($dom[($dom[$key]['parent'])]['parent'])]['cols']))) {
              $dom[($dom[($dom[$key]['parent'])]['parent'])]['cols'] = $dom[($dom[$key]['parent'])]['cols'];
            }
            if (($dom[$key]['value'] == 'td') OR ($dom[$key]['value'] == 'th')) {
              $dom[($dom[$key]['parent'])]['content'] = '';
              for ($i = ($dom[$key]['parent'] + 1); $i < $key; ++$i) {
                $dom[($dom[$key]['parent'])]['content'] .= $a[$dom[$i]['elkey']];
              }
              $key = $i;
            }
            // store header rows on a new table
            if (($dom[$key]['value'] == 'tr') AND ($dom[($dom[$key]['parent'])]['thead'] == true)) {
              if ($this->empty_string($dom[($dom[($dom[$key]['parent'])]['parent'])]['thead'])) {
                $dom[($dom[($dom[$key]['parent'])]['parent'])]['thead'] = $a[$dom[($dom[($dom[$key]['parent'])]['parent'])]['elkey']];
              }
              for ($i = $dom[$key]['parent']; $i <= $key; ++$i) {
                $dom[($dom[($dom[$key]['parent'])]['parent'])]['thead'] .= $a[$dom[$i]['elkey']];
              }
            }
            if (($dom[$key]['value'] == 'table') AND (!$this->empty_string($dom[($dom[$key]['parent'])]['thead']))) {
              $dom[($dom[$key]['parent'])]['thead'] .= '</table>';
            }
          } else {
            // opening html tag
            $dom[$key]['opening'] = true;
            $dom[$key]['parent'] = end($level);
            if (substr($element, -1, 1) != '/') {
              // not self-closing tag
              array_push($level, $key);
              $dom[$key]['self'] = false;
            } else {
              $dom[$key]['self'] = true;
            }
            // copy some values from parent
            $parentkey = 0;
            if ($key > 0) {
              $parentkey = $dom[$key]['parent'];
              $dom[$key]['fontname'] = $dom[$parentkey]['fontname'];
              $dom[$key]['fontstyle'] = $dom[$parentkey]['fontstyle'];
              $dom[$key]['fontsize'] = $dom[$parentkey]['fontsize'];
              $dom[$key]['bgcolor'] = $dom[$parentkey]['bgcolor'];
              $dom[$key]['fgcolor'] = $dom[$parentkey]['fgcolor'];
              $dom[$key]['align'] = $dom[$parentkey]['align'];
              $dom[$key]['listtype'] = $dom[$parentkey]['listtype'];
            }
            // get attributes
            preg_match_all('/([^=\s]*)=["]?([^"]*)["]?/', $element, $attr_array, PREG_PATTERN_ORDER);
            $dom[$key]['attribute'] = array(); // reset attribute array
            while (list($id, $name) = each($attr_array[1])) {
              $dom[$key]['attribute'][strtolower($name)] = $attr_array[2][$id];
            }
            // split style attributes
            if (isset($dom[$key]['attribute']['style'])) {
              // get style attributes
              preg_match_all('/([^;:\s]*):([^;]*)/', $dom[$key]['attribute']['style'], $style_array, PREG_PATTERN_ORDER);
              $dom[$key]['style'] = array(); // reset style attribute array
              while (list($id, $name) = each($style_array[1])) {
                $dom[$key]['style'][strtolower($name)] = trim($style_array[2][$id]);
              }
              // --- get some style attributes ---
              if (isset($dom[$key]['style']['font-family'])) {
                // font family
                if (isset($dom[$key]['style']['font-family'])) {
                  $fontslist = explode(',', strtolower($dom[$key]['style']['font-family']));
                  foreach ($fontslist as $font) {
                    $font = trim(strtolower($font));
                    if (in_array($font, $this->fontlist) OR in_array($font, $this->fontkeys)) {
                      $dom[$key]['fontname'] = $font;
                      break;
                    }
                  }
                }
              }
              // list-style-type
              if (isset($dom[$key]['style']['list-style-type'])) {
                $dom[$key]['listtype'] = trim(strtolower($dom[$key]['style']['list-style-type']));
                if ($dom[$key]['listtype'] == 'inherit') {
                  $dom[$key]['listtype'] = $dom[$parentkey]['listtype'];
                }
              }
              // font size
              if (isset($dom[$key]['style']['font-size'])) {
                $fsize = trim($dom[$key]['style']['font-size']);
                switch ($fsize) {
                  // absolute-size
                  case 'xx-small': {
                    $dom[$key]['fontsize'] = $dom[0]['fontsize'] - 4;
                    break;
                  }
                  case 'x-small': {
                    $dom[$key]['fontsize'] = $dom[0]['fontsize'] - 3;
                    break;
                  }
                  case 'small': {
                    $dom[$key]['fontsize'] = $dom[0]['fontsize'] - 2;
                    break;
                  }
                  case 'medium': {
                    $dom[$key]['fontsize'] = $dom[0]['fontsize'];
                    break;
                  }
                  case 'large': {
                    $dom[$key]['fontsize'] = $dom[0]['fontsize'] + 2;
                    break;
                  }
                  case 'x-large': {
                    $dom[$key]['fontsize'] = $dom[0]['fontsize'] + 4;
                    break;
                  }
                  case 'xx-large': {
                    $dom[$key]['fontsize'] = $dom[0]['fontsize'] + 6;
                    break;
                  }
                  // relative-size
                  case 'smaller': {
                    $dom[$key]['fontsize'] = $dom[$parentkey]['fontsize'] - 3;
                    break;
                  }
                  case 'larger': {
                    $dom[$key]['fontsize'] = $dom[$parentkey]['fontsize'] + 3;
                    break;
                  }
                  default: {
                    $dom[$key]['fontsize'] = $this->getHTMLUnitToUnits($fsize, $dom[$parentkey]['fontsize'], 'pt', true);
                  }
                }
              }
              // font style
              if (isset($dom[$key]['style']['font-weight']) AND (strtolower($dom[$key]['style']['font-weight']{0}) == 'b')) {
                $dom[$key]['fontstyle'] .= 'B';
              }
              if (isset($dom[$key]['style']['font-style']) AND (strtolower($dom[$key]['style']['font-style']{0}) == 'i')) {
                $dom[$key]['fontstyle'] .= '"I';
              }
              // font color
              if (isset($dom[$key]['style']['color']) AND (!$this->empty_string($dom[$key]['style']['color']))) {
                $dom[$key]['fgcolor'] = $this->convertHTMLColorToDec($dom[$key]['style']['color']);
              }
              // background color
              if (isset($dom[$key]['style']['background-color']) AND (!$this->empty_string($dom[$key]['style']['background-color']))) {
                $dom[$key]['bgcolor'] = $this->convertHTMLColorToDec($dom[$key]['style']['background-color']);
              }
              // text-decoration
              if (isset($dom[$key]['style']['text-decoration'])) {
                $decors = explode(' ', strtolower($dom[$key]['style']['text-decoration']));
                foreach ($decors as $dec) {
                  $dec = trim($dec);
                  if (!$this->empty_string($dec)) {
                    if ($dec{0} == 'u') {
                      $dom[$key]['fontstyle'] .= 'U';
                    } elseif ($dec{0} == 'l') {
                      $dom[$key]['fontstyle'] .= 'D';
                    }
                  }
                }
              }
              // check for width attribute
              if (isset($dom[$key]['style']['width'])) {
                $dom[$key]['width'] = $dom[$key]['style']['width'];
              }
              // check for height attribute
              if (isset($dom[$key]['style']['height'])) {
                $dom[$key]['height'] = $dom[$key]['style']['height'];
              }
              // check for text alignment
              if (isset($dom[$key]['style']['text-align'])) {
                $dom[$key]['align'] = strtoupper($dom[$key]['style']['text-align']{0});
              }
              // check for border attribute
              if (isset($dom[$key]['style']['border'])) {
                $dom[$key]['attribute']['border'] = $dom[$key]['style']['border'];
              }
            }
            // check for font tag
            if ($dom[$key]['value'] == 'font') {
              // font family
              if (isset($dom[$key]['attribute']['face'])) {
                $fontslist = split(',', strtolower($dom[$key]['attribute']['face']));
                foreach ($fontslist as $font) {
                  $font = trim(strtolower($font));
                  if (in_array($font, $this->fontlist) OR in_array($font, $this->fontkeys)) {
                    $dom[$key]['fontname'] = $font;
                    break;
                  }
                }
              }
              // font size
              if (isset($dom[$key]['attribute']['size'])) {
                if ($key > 0) {
                  if ($dom[$key]['attribute']['size']{0} == '+') {
                    $dom[$key]['fontsize'] = $dom[($dom[$key]['parent'])]['fontsize'] + intval(substr($dom[$key]['attribute']['size'], 1));
                  } elseif ($dom[$key]['attribute']['size']{0} == '-') {
                    $dom[$key]['fontsize'] = $dom[($dom[$key]['parent'])]['fontsize'] - intval(substr($dom[$key]['attribute']['size'], 1));
                  } else {
                    $dom[$key]['fontsize'] = intval($dom[$key]['attribute']['size']);
                  }
                } else {
                  $dom[$key]['fontsize'] = intval($dom[$key]['attribute']['size']);
                }
              }
            }
            // force natural alignment for lists
            if ((($dom[$key]['value'] == 'ul') OR ($dom[$key]['value'] == 'ol') OR ($dom[$key]['value'] == 'dl'))
              AND (!isset($dom[$key]['align']) OR $this->empty_string($dom[$key]['align']) OR ($dom[$key]['align'] != 'J'))) {
              if ($this->rtl) {
                $dom[$key]['align'] = 'R';
              } else {
                $dom[$key]['align'] = 'L';
              }
            }
            if (($dom[$key]['value'] == 'small') OR ($dom[$key]['value'] == 'sup') OR ($dom[$key]['value'] == 'sub')) {
              $dom[$key]['fontsize'] = $dom[$key]['fontsize'] * K_SMALL_RATIO;
            }
            if (($dom[$key]['value'] == 'strong') OR ($dom[$key]['value'] == 'b')) {
              $dom[$key]['fontstyle'] .= 'B';
            }
            if (($dom[$key]['value'] == 'em') OR ($dom[$key]['value'] == 'i')) {
              $dom[$key]['fontstyle'] .= 'I';
            }
            if ($dom[$key]['value'] == 'u') {
              $dom[$key]['fontstyle'] .= 'U';
            }
            if ($dom[$key]['value'] == 'del') {
              $dom[$key]['fontstyle'] .= 'D';
            }
            if (($dom[$key]['value'] == 'pre') OR ($dom[$key]['value'] == 'tt')) {
              $dom[$key]['fontname'] = $this->default_monospaced_font;
            }
            if (($dom[$key]['value']{0} == 'h') AND (intval($dom[$key]['value']{1}) > 0) AND (intval($dom[$key]['value']{1}) < 7)) {
              $headsize = (4 - intval($dom[$key]['value']{1})) * 2;
              $dom[$key]['fontsize'] = $dom[0]['fontsize'] + $headsize;
              $dom[$key]['fontstyle'] .= 'B';
            }
            if (($dom[$key]['value'] == 'table')) {
              $dom[$key]['rows'] = 0; // number of rows
              $dom[$key]['trids'] = array(); // IDs of TR elements
              $dom[$key]['thead'] = ''; // table header rows
            }
            if (($dom[$key]['value'] == 'tr')) {
              $dom[$key]['cols'] = 0;
              // store the number of rows on table element
              ++$dom[($dom[$key]['parent'])]['rows'];
              // store the TR elements IDs on table element
              array_push($dom[($dom[$key]['parent'])]['trids'], $key);
              if ($thead) {
                $dom[$key]['thead'] = true;
              } else {
                $dom[$key]['thead'] = false;
              }
            }
            if (($dom[$key]['value'] == 'th') OR ($dom[$key]['value'] == 'td')) {
              if (isset($dom[$key]['attribute']['colspan'])) {
                $colspan = intval($dom[$key]['attribute']['colspan']);
              } else {
                $colspan = 1;
              }
              $dom[$key]['attribute']['colspan'] = $colspan;
              $dom[($dom[$key]['parent'])]['cols'] += $colspan;
            }
            // set foreground color attribute
            if (isset($dom[$key]['attribute']['color']) AND (!$this->empty_string($dom[$key]['attribute']['color']))) {
              $dom[$key]['fgcolor'] = $this->convertHTMLColorToDec($dom[$key]['attribute']['color']);
            }
            // set background color attribute
            if (isset($dom[$key]['attribute']['bgcolor']) AND (!$this->empty_string($dom[$key]['attribute']['bgcolor']))) {
              $dom[$key]['bgcolor'] = $this->convertHTMLColorToDec($dom[$key]['attribute']['bgcolor']);
            }
            // check for width attribute
            if (isset($dom[$key]['attribute']['width'])) {
              $dom[$key]['width'] = $dom[$key]['attribute']['width'];
            }
            // check for height attribute
            if (isset($dom[$key]['attribute']['height'])) {
              $dom[$key]['height'] = $dom[$key]['attribute']['height'];
            }
            // check for text alignment
            if (isset($dom[$key]['attribute']['align']) AND (!$this->empty_string($dom[$key]['attribute']['align'])) AND ($dom[$key]['value'] !== 'img')) {
              $dom[$key]['align'] = strtoupper($dom[$key]['attribute']['align']{0});
            }
          } // end opening tag
        } else {
          // text
          $dom[$key]['tag'] = false;
          $dom[$key]['value'] = stripslashes($this->unhtmlentities($element));
          $dom[$key]['parent'] = end($level);
        }
        ++$elkey;
        ++$key;
      }
      return $dom;
    }

    /**
     * Set the default bullet to be used as LI bullet symbol
     * @param string $symbol character or string to be used (legal values are: '' = automatic, '!' = auto bullet, '#' = auto numbering, 'disc', 'disc', 'circle', 'square', '1', 'decimal', 'decimal-leading-zero', 'i', 'lower-roman', 'I', 'upper-roman', 'a', 'lower-alpha', 'lower-latin', 'A', 'upper-alpha', 'upper-latin', 'lower-greek')
     * @access public
     * @since 4.0.028 (2008-09-26)
     */
    public function setLIsymbol($symbol='!') {
      $symbol = strtolower($symbol);
      switch ($symbol) {
        case '!' :
        case '#' :
        case 'disc' :
        case 'disc' :
        case 'circle' :
        case 'square' :
        case '1':
        case 'decimal':
        case 'decimal-leading-zero':
        case 'i':
        case 'lower-roman':
        case 'I':
        case 'upper-roman':
        case 'a':
        case 'lower-alpha':
        case 'lower-latin':
        case 'A':
        case 'upper-alpha':
        case 'upper-latin':
        case 'lower-greek': {
          $this->lisymbol = $symbol;
          break;
        }
        default : {
          $this->lisymbol = '';
        }
      }
    }

    /**
    * Set the vertical spaces for HTML tags.
    * The array must have the following structure (example):
    * $tagvs = array('h1' => array(0 => array('h' => '', 'n' => 2), 1 => array('h' => 1.3, 'n' => 1)));
    * The first array level contains the tag names,
    * the second level contains 0 for opening tags or 1 for closing tags,
    * the third level contains the vertical space unit (h) and the number spaces to add (n).
    * If the h parameter is not specified, default values are used.
    * @param array $tagvs array of tags and relative vertical spaces.
    * @access public
    * @since 4.2.001 (2008-10-30)
    */
    public function setHtmlVSpace($tagvs) {
      $this->tagvspaces = $tagvs;
    }

