<?php
/*******************************************************************************
* FPDF                                                                         *
* Version: 1.86                                                                *
* Website: http://www.fpdf.org                                                 *
* License: Freeware                                                            *
*******************************************************************************/

define('FPDF_VERSION','1.86');

class FPDF
{
protected $page;
protected $n;
protected $offsets;
protected $buffer;
protected $pages;
protected $state;
protected $compress;
protected $k;
protected $DefOrientation;
protected $CurOrientation;
protected $StdPageSizes;
protected $DefPageSize;
protected $CurPageSize;
protected $CurRotation;
protected $PageInfo;
protected $wPt, $hPt;
protected $w, $h;
protected $lMargin;
protected $tMargin;
protected $rMargin;
protected $bMargin;
protected $cMargin;
protected $x, $y;
protected $lasth;
protected $LineWidth;
protected $fontpath;
protected $CoreFonts;
protected $fonts;
protected $FontFiles;
protected $encodings;
protected $cmaps;
protected $FontFamily;
protected $FontStyle;
protected $underline;
protected $CurrentFont;
protected $FontSizePt;
protected $FontSize;
protected $DrawColor;
protected $FillColor;
protected $TextColor;
protected $ColorFlag;
protected $WithAlpha;
protected $ws;
protected $images;
protected $PageLinks;
protected $links;
protected $AutoPageBreak;
protected $PageBreakTrigger;
protected $InHeader;
protected $InFooter;
protected $AliasNbPages;
protected $ZoomMode;
protected $LayoutMode;
protected $metadata;
protected $CreationDate;
protected $PDFVersion;

function __construct($orientation='P', $unit='mm', $size='A4')
{
    $this->state = 0;
    $this->page = 0;
    $this->n = 2;
    $this->buffer = '';
    $this->pages = array();
    $this->PageInfo = array();
    $this->fonts = array();
    $this->FontFiles = array();
    $this->encodings = array();
    $this->cmaps = array();
    $this->images = array();
    $this->links = array();
    $this->InHeader = false;
    $this->InFooter = false;
    $this->lasth = 0;
    $this->FontFamily = '';
    $this->FontStyle = '';
    $this->FontSizePt = 12;
    $this->underline = false;
    $this->DrawColor = '0 G';
    $this->FillColor = '0 g';
    $this->TextColor = '0 g';
    $this->ColorFlag = false;
    $this->WithAlpha = false;
    $this->ws = 0;
    $this->fontpath = defined('FPDF_FONTPATH') ? FPDF_FONTPATH : dirname(__FILE__).'/font/';
    $this->CoreFonts = array('courier', 'helvetica', 'times', 'symbol', 'zapfdingbats');
    if($unit=='pt') $this->k = 1;
    elseif($unit=='mm') $this->k = 72/25.4;
    elseif($unit=='cm') $this->k = 72/2.54;
    elseif($unit=='in') $this->k = 72;
    else $this->Error('Incorrect unit: '.$unit);
    if(is_string($size)) {
        $size = $this->_getpagesize($size);
    }
    $this->DefPageSize = $size;
    $this->CurPageSize = $size;
    $orientation = strtolower($orientation);
    if($orientation=='p' || $orientation=='portrait') {
        $this->DefOrientation = 'P';
        $this->w = $size[0];
        $this->h = $size[1];
    }
    elseif($orientation=='l' || $orientation=='landscape') {
        $this->DefOrientation = 'L';
        $this->w = $size[1];
        $this->h = $size[0];
    }
    else $this->Error('Incorrect orientation: '.$orientation);
    $this->CurOrientation = $this->DefOrientation;
    $this->wPt = $this->w*$this->k;
    $this->hPt = $this->h*$this->k;
    $this->CurRotation = 0;
    $margin = 28.35/$this->k;
    $this->SetMargins($margin,$margin);
    $this->cMargin = $margin/10;
    $this->LineWidth = .567/$this->k;
    $this->SetAutoPageBreak(true,2*$margin);
    $this->SetDisplayMode('default');
    $this->SetCompression(true);
    $this->metadata = array('Producer'=>'FPDF '.FPDF_VERSION);
    $this->CreationDate = @date('YmdHis');
    $this->PDFVersion = '1.3';
}

function SetMargins($left, $top, $right=null) { $this->lMargin = $left; $this->tMargin = $top; if($right===null) $right = $left; $this->rMargin = $right; }
function SetLeftMargin($margin) { $this->lMargin = $margin; if($this->page>0 && $this->x<$margin) $this->x = $margin; }
function SetTopMargin($margin) { $this->tMargin = $margin; }
function SetRightMargin($margin) { $this->rMargin = $margin; }
function SetAutoPageBreak($auto, $margin=0) { $this->AutoPageBreak = $auto; $this->bMargin = $margin; $this->PageBreakTrigger = $this->h-$margin; }
function SetDisplayMode($zoom, $layout='default') { $this->ZoomMode = $zoom; $this->LayoutMode = $layout; }
function SetCompression($compress) { $this->compress = $compress; }
function SetTitle($title, $isUTF8=false) { $this->metadata['Title'] = $isUTF8 ? $title : $this->_UTF8encode($title); }
function SetAuthor($author, $isUTF8=false) { $this->metadata['Author'] = $isUTF8 ? $author : $this->_UTF8encode($author); }
function SetSubject($subject, $isUTF8=false) { $this->metadata['Subject'] = $isUTF8 ? $subject : $this->_UTF8encode($subject); }
function SetKeywords($keywords, $isUTF8=false) { $this->metadata['Keywords'] = $isUTF8 ? $keywords : $this->_UTF8encode($keywords); }
function SetCreator($creator, $isUTF8=false) { $this->metadata['Creator'] = $isUTF8 ? $creator : $this->_UTF8encode($creator); }
function AliasNbPages($alias='{nb}') { $this->AliasNbPages = $alias; }
function Error($msg) { throw new Exception('FPDF error: '.$msg); }
function Close() { if($this->state==3) return; if($this->page==0) $this->AddPage(); $this->InFooter = true; $this->Footer(); $this->InFooter = false; $this->_endpage(); $this->_enddoc(); }
function AddPage($orientation='', $size='', $rotation=0) { if($this->state==3) $this->Error('The document is closed'); $family = $this->FontFamily; $style = $this->FontStyle.($this->underline ? 'U' : ''); $fontsize = $this->FontSizePt; $lw = $this->LineWidth; $dc = $this->DrawColor; $fc = $this->FillColor; $tc = $this->TextColor; $cf = $this->ColorFlag; if($this->page>0) { $this->InFooter = true; $this->Footer(); $this->InFooter = false; $this->_endpage(); } $this->_beginpage($orientation,$size,$rotation); $this->_out('2 J'); $this->LineWidth = $lw; $this->_out(sprintf('%.2F w',$lw*$this->k)); if($family) $this->SetFont($family,$style,$fontsize); $this->DrawColor = $dc; if($dc!='0 G') $this->_out($dc); $this->FillColor = $fc; if($fc!='0 g') $this->_out($fc); $this->TextColor = $tc; $this->ColorFlag = $cf; $this->InHeader = true; $this->Header(); $this->InHeader = false; if($this->LineWidth!=$lw) { $this->LineWidth = $lw; $this->_out(sprintf('%.2F w',$lw*$this->k)); } if($family) $this->SetFont($family,$style,$fontsize); if($this->DrawColor!=$dc) { $this->DrawColor = $dc; $this->_out($dc); } if($this->FillColor!=$fc) { $this->FillColor = $fc; $this->_out($fc); } $this->TextColor = $tc; $this->ColorFlag = $cf; }
function Header() { }
function Footer() { }
function PageNo() { return $this->page; }
function SetDrawColor($r, $g=null, $b=null) { if(($r==0 && $g==0 && $b==0) || $g===null) $this->DrawColor = sprintf('%.3F G',$r/255); else $this->DrawColor = sprintf('%.3F %.3F %.3F RG',$r/255,$g/255,$b/255); if($this->page>0) $this->_out($this->DrawColor); }
function SetFillColor($r, $g=null, $b=null) { if(($r==0 && $g==0 && $b==0) || $g===null) $this->FillColor = sprintf('%.3F g',$r/255); else $this->FillColor = sprintf('%.3F %.3F %.3F rg',$r/255,$g/255,$b/255); $this->ColorFlag = ($this->FillColor!=$this->TextColor); if($this->page>0) $this->_out($this->FillColor); }
function SetTextColor($r, $g=null, $b=null) { if(($r==0 && $g==0 && $b==0) || $g===null) $this->TextColor = sprintf('%.3F g',$r/255); else $this->TextColor = sprintf('%.3F %.3F %.3F rg',$r/255,$g/255,$b/255); $this->ColorFlag = ($this->FillColor!=$this->TextColor); }
function GetStringWidth($s) { $s = (string)$s; $cw = $this->CurrentFont['cw']; $w = 0; $l = strlen($s); for($i=0;$i<$l;$i++) { $c = $s[$i]; $w += isset($cw[$c]) ? $cw[$c] : 600; } return $w*$this->FontSize/1000; }
function SetLineWidth($width) { $this->LineWidth = $width; if($this->page>0) $this->_out(sprintf('%.2F w',$width*$this->k)); }
function Line($x1, $y1, $x2, $y2) { $this->_out(sprintf('%.2F %.2F m %.2F %.2F l S',$x1*$this->k,($this->h-$y1)*$this->k,$x2*$this->k,($this->h-$y2)*$this->k)); }
function Rect($x, $y, $w, $h, $style='') { if($style=='F') $op = 'f'; elseif($style=='FD' || $style=='DF') $op = 'B'; else $op = 'S'; $this->_out(sprintf('%.2F %.2F %.2F %.2F re %s',$x*$this->k,($this->h-$y)*$this->k,$w*$this->k,-$h*$this->k,$op)); }
function AddFont($family, $style='', $file='', $dir='') { $family = strtolower($family); $style = strtoupper($style); if($style=='IB') $style = 'BI'; $fontkey = $family.$style; if(isset($this->fonts[$fontkey])) return; if(in_array($family, $this->CoreFonts)) { $this->_addCoreFont($family, $style); return; } if($file=='') $file = str_replace(' ','',$family).strtolower($style).'.php'; if($dir=='') $dir = $this->fontpath; @include($dir.$file); if(!isset($name)) $this->Error('Could not include font definition file'); $i = count($this->fonts)+1; $this->fonts[$fontkey] = array('i'=>$i, 'type'=>$type, 'name'=>$name, 'desc'=>$desc, 'up'=>$up, 'ut'=>$ut, 'cw'=>$cw, 'enc'=>$enc, 'file'=>$file); if($file) { if($type=='TrueType') $this->FontFiles[$file] = array('length1'=>$originalsize); else $this->FontFiles[$file] = array('length1'=>$size1, 'length2'=>$size2); } }
function _addCoreFont($family, $style) { $name = $family; if($family=='times') $name = 'Times'; elseif($family=='helvetica') $name = 'Helvetica'; elseif($family=='courier') $name = 'Courier'; elseif($family=='symbol') $name = 'Symbol'; elseif($family=='zapfdingbats') $name = 'ZapfDingbats'; if($style=='B') $name .= '-Bold'; elseif($style=='I') $name .= ($family=='times' ? '-Italic' : '-Oblique'); elseif($style=='BI') $name .= ($family=='times' ? '-BoldItalic' : '-BoldOblique'); $fontkey = $family.$style; $cw = $this->_getCoreCharWidths($family, $style); $i = count($this->fonts)+1; $this->fonts[$fontkey] = array('i'=>$i, 'type'=>'Core', 'name'=>$name, 'up'=>-100, 'ut'=>50, 'cw'=>$cw); }
function _getCoreCharWidths($family, $style) { $cw = array(); for($i=0;$i<256;$i++) $cw[chr($i)] = 600; if($family=='courier') return $cw; $widths = array('helvetica'=>array(278,278,355,556,556,889,667,191,333,333,389,584,278,333,278,278,556,556,556,556,556,556,556,556,556,556,278,278,584,584,584,556,1015,667,667,722,722,667,611,778,722,278,500,667,556,833,722,778,667,778,722,667,611,722,667,944,667,667,611,278,278,278,469,556,333,556,556,500,556,556,278,556,556,222,222,500,222,833,556,556,556,556,333,500,278,556,500,722,500,500,500,334,260,334,584,350,350,556,556,500,556,556,278,556,556,222,222,500,222,833,556,556,556,556,333,500,278,556,500,722,500,500,500,334,260,334,584,350,278,278,333,556,556,556,556,556,556,556,556,556,556,556,556,278,333,333,584,556,556,556,556,556,556,556,556,556,556,556,556,556,333,260,556,556,556,556,280,556,333,737,370,556,584,333,737,333,400,584,333,333,333,556,537,278,333,333,365,556,834,834,834,611,667,667,667,667,667,667,1000,722,667,667,667,667,278,278,278,278,722,722,778,778,778,778,778,584,778,722,722,722,722,667,667,611,556,556,556,556,556,556,889,500,556,556,556,556,278,278,278,278,556,556,556,556,556,556,556,584,611,556,556,556,556,500,556,500), 'times'=>array(250,333,408,500,500,833,778,180,333,333,500,564,250,333,250,278,500,500,500,500,500,500,500,500,500,500,278,278,564,564,564,444,921,722,667,667,722,611,556,722,722,333,389,722,611,889,722,722,556,722,667,556,611,722,722,944,722,722,611,333,278,333,469,500,333,444,500,444,500,444,333,500,500,278,278,500,278,778,500,500,500,500,333,389,278,500,500,722,500,500,444,480,200,480,541,350,350,500,500,444,500,444,333,500,500,278,278,500,278,778,500,500,500,500,333,389,278,500,500,722,500,500,444,480,200,480,541,350)); if(isset($widths[$family])) { $w = $widths[$family]; $len = count($w); for($i=0;$i<$len;$i++) $cw[chr($i+32)] = $w[$i]; } return $cw; }
function SetFont($family, $style='', $size=0) { if($family=='') $family = $this->FontFamily; else $family = strtolower($family); $style = strtoupper($style); if(strpos($style,'U')!==false) { $this->underline = true; $style = str_replace('U','',$style); } else $this->underline = false; if($style=='IB') $style = 'BI'; if($size==0) $size = $this->FontSizePt; if($this->FontFamily==$family && $this->FontStyle==$style && $this->FontSizePt==$size) return; $fontkey = $family.$style; if(!isset($this->fonts[$fontkey])) { if(in_array($family,$this->CoreFonts)) { if($family=='symbol' || $family=='zapfdingbats') $style = ''; $fontkey = $family.$style; if(!isset($this->fonts[$fontkey])) $this->AddFont($family,$style); } else $this->Error('Undefined font: '.$family.' '.$style); } $this->FontFamily = $family; $this->FontStyle = $style; $this->FontSizePt = $size; $this->FontSize = $size/$this->k; $this->CurrentFont = $this->fonts[$fontkey]; if($this->page>0) $this->_out(sprintf('BT /F%d %.2F Tf ET',$this->CurrentFont['i'],$this->FontSizePt)); }
function SetFontSize($size) { if($this->FontSizePt==$size) return; $this->FontSizePt = $size; $this->FontSize = $size/$this->k; if($this->page>0) $this->_out(sprintf('BT /F%d %.2F Tf ET',$this->CurrentFont['i'],$this->FontSizePt)); }
function AddLink() { $n = count($this->links)+1; $this->links[$n] = array(0, 0); return $n; }
function SetLink($link, $y=0, $page=-1) { if($y==-1) $y = $this->y; if($page==-1) $page = $this->page; $this->links[$link] = array($page, $y); }
function Link($x, $y, $w, $h, $link) { $this->PageLinks[$this->page][] = array($x*$this->k, $this->hPt-$y*$this->k, $w*$this->k, $h*$this->k, $link); }
function Text($x, $y, $txt) { $txt = (string)$txt; $s = sprintf('BT %.2F %.2F Td (%s) Tj ET',$x*$this->k,($this->h-$y)*$this->k,$this->_escape($txt)); if($this->underline && $txt!='') $s .= ' '.$this->_dounderline($x,$y,$txt); if($this->ColorFlag) $s = 'q '.$this->TextColor.' '.$s.' Q'; $this->_out($s); }
function AcceptPageBreak() { return $this->AutoPageBreak; }
function Cell($w, $h=0, $txt='', $border=0, $ln=0, $align='', $fill=false, $link='') { $txt = (string)$txt; $k = $this->k; if($this->y+$h>$this->PageBreakTrigger && !$this->InHeader && !$this->InFooter && $this->AcceptPageBreak()) { $x = $this->x; $ws = $this->ws; if($ws>0) { $this->ws = 0; $this->_out('0 Tw'); } $this->AddPage($this->CurOrientation,$this->CurPageSize,$this->CurRotation); $this->x = $x; if($ws>0) { $this->ws = $ws; $this->_out(sprintf('%.3F Tw',$ws*$k)); } } if($w==0) $w = $this->w-$this->rMargin-$this->x; $s = ''; if($fill || $border==1) { if($fill) $op = ($border==1) ? 'B' : 'f'; else $op = 'S'; $s = sprintf('%.2F %.2F %.2F %.2F re %s ',$this->x*$k,($this->h-$this->y)*$k,$w*$k,-$h*$k,$op); } if(is_string($border)) { $x = $this->x; $y = $this->y; if(strpos($border,'L')!==false) $s .= sprintf('%.2F %.2F m %.2F %.2F l S ',$x*$k,($this->h-$y)*$k,$x*$k,($this->h-($y+$h))*$k); if(strpos($border,'T')!==false) $s .= sprintf('%.2F %.2F m %.2F %.2F l S ',$x*$k,($this->h-$y)*$k,($x+$w)*$k,($this->h-$y)*$k); if(strpos($border,'R')!==false) $s .= sprintf('%.2F %.2F m %.2F %.2F l S ',($x+$w)*$k,($this->h-$y)*$k,($x+$w)*$k,($this->h-($y+$h))*$k); if(strpos($border,'B')!==false) $s .= sprintf('%.2F %.2F m %.2F %.2F l S ',$x*$k,($this->h-($y+$h))*$k,($x+$w)*$k,($this->h-($y+$h))*$k); } if($txt!=='') { if(!isset($this->CurrentFont)) $this->Error('No font has been set'); if($align=='R') $dx = $w-$this->cMargin-$this->GetStringWidth($txt); elseif($align=='C') $dx = ($w-$this->GetStringWidth($txt))/2; else $dx = $this->cMargin; if($this->ColorFlag) $s .= 'q '.$this->TextColor.' '; $s .= sprintf('BT %.2F %.2F Td (%s) Tj ET',($this->x+$dx)*$k,($this->h-($this->y+.5*$h+.3*$this->FontSize))*$k,$this->_escape($txt)); if($this->underline) $s .= ' '.$this->_dounderline($this->x+$dx,$this->y+.5*$h+.3*$this->FontSize,$txt); if($this->ColorFlag) $s .= ' Q'; if($link) $this->Link($this->x+$dx,$this->y+.5*$h-.5*$this->FontSize,$this->GetStringWidth($txt),$this->FontSize,$link); } if($s) $this->_out($s); $this->lasth = $h; if($ln>0) { $this->y += $h; if($ln==1) $this->x = $this->lMargin; } else $this->x += $w; }
function MultiCell($w, $h, $txt, $border=0, $align='J', $fill=false) { $txt = (string)$txt; if(!isset($this->CurrentFont)) $this->Error('No font has been set'); $cw = $this->CurrentFont['cw']; if($w==0) $w = $this->w-$this->rMargin-$this->x; $wmax = ($w-2*$this->cMargin)*1000/$this->FontSize; $s = str_replace("\r",'',$txt); $nb = strlen($s); if($nb>0 && $s[$nb-1]=="\n") $nb--; $b = 0; if($border) { if($border==1) { $border = 'LTRB'; $b = 'LRT'; $b2 = 'LR'; } else { $b2 = ''; if(strpos($border,'L')!==false) $b2 .= 'L'; if(strpos($border,'R')!==false) $b2 .= 'R'; $b = (strpos($border,'T')!==false) ? $b2.'T' : $b2; } } $sep = -1; $i = 0; $j = 0; $l = 0; $ns = 0; $nl = 1; while($i<$nb) { $c = $s[$i]; if($c=="\n") { if($this->ws>0) { $this->ws = 0; $this->_out('0 Tw'); } $this->Cell($w,$h,substr($s,$j,$i-$j),$b,2,$align,$fill); $i++; $sep = -1; $j = $i; $l = 0; $ns = 0; $nl++; if($border && $nl==2) $b = $b2; continue; } if($c==' ') { $sep = $i; $ls = $l; $ns++; } $l += $cw[$c]; if($l>$wmax) { if($sep==-1) { if($i==$j) $i++; if($this->ws>0) { $this->ws = 0; $this->_out('0 Tw'); } $this->Cell($w,$h,substr($s,$j,$i-$j),$b,2,$align,$fill); } else { if($align=='J') { $this->ws = ($ns>1) ? ($wmax-$ls)/1000*$this->FontSize/($ns-1) : 0; $this->_out(sprintf('%.3F Tw',$this->ws*$this->k)); } $this->Cell($w,$h,substr($s,$j,$sep-$j),$b,2,$align,$fill); $i = $sep+1; } $sep = -1; $j = $i; $l = 0; $ns = 0; $nl++; if($border && $nl==2) $b = $b2; } else $i++; } if($this->ws>0) { $this->ws = 0; $this->_out('0 Tw'); } if($border && strpos($border,'B')!==false) $b .= 'B'; $this->Cell($w,$h,substr($s,$j,$i-$j),$b,2,$align,$fill); $this->x = $this->lMargin; }
function Write($h, $txt, $link='') { $txt = (string)$txt; if(!isset($this->CurrentFont)) $this->Error('No font has been set'); $cw = $this->CurrentFont['cw']; $w = $this->w-$this->rMargin-$this->x; $wmax = ($w-2*$this->cMargin)*1000/$this->FontSize; $s = str_replace("\r",'',$txt); $nb = strlen($s); $sep = -1; $i = 0; $j = 0; $l = 0; $nl = 1; while($i<$nb) { $c = $s[$i]; if($c=="\n") { $this->Cell($w,$h,substr($s,$j,$i-$j),0,2,'',false,$link); $i++; $sep = -1; $j = $i; $l = 0; if($nl==1) { $this->x = $this->lMargin; $w = $this->w-$this->rMargin-$this->x; $wmax = ($w-2*$this->cMargin)*1000/$this->FontSize; } $nl++; continue; } if($c==' ') $sep = $i; $l += $cw[$c]; if($l>$wmax) { if($sep==-1) { if($this->x>$this->lMargin) { $this->x = $this->lMargin; $this->y += $h; $w = $this->w-$this->rMargin-$this->x; $wmax = ($w-2*$this->cMargin)*1000/$this->FontSize; $i++; $nl++; continue; } if($i==$j) $i++; $this->Cell($w,$h,substr($s,$j,$i-$j),0,2,'',false,$link); } else { $this->Cell($w,$h,substr($s,$j,$sep-$j),0,2,'',false,$link); $i = $sep+1; } $sep = -1; $j = $i; $l = 0; if($nl==1) { $this->x = $this->lMargin; $w = $this->w-$this->rMargin-$this->x; $wmax = ($w-2*$this->cMargin)*1000/$this->FontSize; } $nl++; } else $i++; } if($i!=$j) $this->Cell($l/1000*$this->FontSize,$h,substr($s,$j),0,0,'',false,$link); }
function Ln($h=null) { $this->x = $this->lMargin; if($h===null) $this->y += $this->lasth; else $this->y += $h; }
function Image($file, $x=null, $y=null, $w=0, $h=0, $type='', $link='') { if($file=='') $this->Error('Image file name is empty'); if(!isset($this->images[$file])) { if($type=='') { $pos = strrpos($file,'.'); if(!$pos) $this->Error('Image file has no extension and no type was specified: '.$file); $type = substr($file,$pos+1); } $type = strtolower($type); if($type=='jpeg') $type = 'jpg'; $mtd = '_parse'.$type; if(!method_exists($this,$mtd)) $this->Error('Unsupported image type: '.$type); $info = $this->$mtd($file); $info['i'] = count($this->images)+1; $this->images[$file] = $info; } else $info = $this->images[$file]; if($w==0 && $h==0) { $w = -96; $h = -96; } if($w<0) $w = -$info['w']*72/$w/$this->k; if($h<0) $h = -$info['h']*72/$h/$this->k; if($w==0) $w = $h*$info['w']/$info['h']; if($h==0) $h = $w*$info['h']/$info['w']; if($x===null) $x = $this->x; if($y===null) { $y = $this->y; $this->y += $h; } $this->_out(sprintf('q %.2F 0 0 %.2F %.2F %.2F cm /I%d Do Q',$w*$this->k,$h*$this->k,$x*$this->k,($this->h-($y+$h))*$this->k,$info['i'])); if($link) $this->Link($x,$y,$w,$h,$link); }
function GetX() { return $this->x; }
function SetX($x) { if($x>=0) $this->x = $x; else $this->x = $this->w+$x; }
function GetY() { return $this->y; }
function SetY($y, $resetX=true) { if($y>=0) $this->y = $y; else $this->y = $this->h+$y; if($resetX) $this->x = $this->lMargin; }
function SetXY($x, $y) { $this->SetX($x); $this->SetY($y,false); }
function Output($dest='', $name='', $isUTF8=false) { $this->Close(); if(strlen($name)==1 && strlen($dest)!=1) { $tmp = $dest; $dest = $name; $name = $tmp; } if($dest=='') $dest = 'I'; if($name=='') $name = 'doc.pdf'; switch(strtoupper($dest)) { case 'I': $this->_checkoutput(); if(PHP_SAPI!='cli') { header('Content-Type: application/pdf'); header('Content-Disposition: inline; '.$this->_httpencode('filename',$name,$isUTF8)); header('Cache-Control: private, max-age=0, must-revalidate'); header('Pragma: public'); } echo $this->buffer; break; case 'D': $this->_checkoutput(); header('Content-Type: application/pdf'); header('Content-Length: '.strlen($this->buffer)); header('Content-Disposition: attachment; '.$this->_httpencode('filename',$name,$isUTF8)); header('Cache-Control: private, max-age=0, must-revalidate'); header('Pragma: public'); echo $this->buffer; break; case 'F': if(!file_put_contents($name,$this->buffer)) $this->Error('Unable to create output file: '.$name); break; case 'S': return $this->buffer; default: $this->Error('Incorrect output destination: '.$dest); } return ''; }

protected function _dochecks() { if(sprintf('%.1F',1.0)!='1.0') setlocale(LC_NUMERIC,'C'); }
protected function _checkoutput() { if(PHP_SAPI!='cli') { if(headers_sent($file,$line)) $this->Error("Some data has already been output, can't send PDF file (output started at $file:$line)"); } if(ob_get_length()) { if(preg_match('/^(\xEF\xBB\xBF)?\s*$/',ob_get_contents())) { ob_clean(); return; } $this->Error("Some data has already been output, can't send PDF file"); } }
protected function _getpagesize($size) { if(is_string($size)) { $size = strtolower($size); $StdPageSizes = array('a3'=>array(841.89,1190.55), 'a4'=>array(595.28,841.89), 'a5'=>array(420.94,595.28), 'letter'=>array(612,792), 'legal'=>array(612,1008)); if(!isset($StdPageSizes[$size])) $this->Error('Unknown page size: '.$size); $a = $StdPageSizes[$size]; return array($a[0]/$this->k, $a[1]/$this->k); } else { if($size[0]>$size[1]) return array($size[1], $size[0]); else return $size; } }
protected function _beginpage($orientation, $size, $rotation) { $this->page++; $this->pages[$this->page] = ''; $this->PageLinks[$this->page] = array(); $this->state = 2; $this->x = $this->lMargin; $this->y = $this->tMargin; $this->FontFamily = ''; if($orientation=='') $orientation = $this->DefOrientation; else $orientation = strtoupper($orientation[0]); if($size=='') $size = $this->DefPageSize; else $size = $this->_getpagesize($size); if($orientation!=$this->CurOrientation || $size[0]!=$this->CurPageSize[0] || $size[1]!=$this->CurPageSize[1]) { if($orientation=='P') { $this->w = $size[0]; $this->h = $size[1]; } else { $this->w = $size[1]; $this->h = $size[0]; } $this->wPt = $this->w*$this->k; $this->hPt = $this->h*$this->k; $this->PageBreakTrigger = $this->h-$this->bMargin; $this->CurOrientation = $orientation; $this->CurPageSize = $size; } if($orientation!=$this->DefOrientation || $size[0]!=$this->DefPageSize[0] || $size[1]!=$this->DefPageSize[1]) $this->PageInfo[$this->page]['size'] = array($this->wPt, $this->hPt); if($rotation!=0) { if($rotation%90!=0) $this->Error('Incorrect rotation value: '.$rotation); $this->CurRotation = $rotation; $this->PageInfo[$this->page]['rotation'] = $rotation; } }
protected function _endpage() { $this->state = 1; }
protected function _escape($s) { if(strpos($s,'(')!==false || strpos($s,')')!==false || strpos($s,'\\')!==false || strpos($s,"\r")!==false) return str_replace(array('\\','(',')',"\r"), array('\\\\','\\(','\\)','\\r'), $s); else return $s; }
protected function _UTF8encode($s) { $res = ''; $nb = strlen($s); for($i=0;$i<$nb;$i++) { $c = ord($s[$i]); if($c<128) $res .= $s[$i]; elseif($c<192) { } elseif($c<224) { $res .= chr(($c>>2)-48).chr((($c&0x03)<<6)+ord($s[++$i])-128); } } return $res; }
protected function _textstring($s) { return '('.$this->_escape($s).')'; }
protected function _dounderline($x, $y, $txt) { $up = $this->CurrentFont['up']; $ut = $this->CurrentFont['ut']; $w = $this->GetStringWidth($txt)+$this->ws*substr_count($txt,' '); return sprintf('%.2F %.2F %.2F %.2F re f',$x*$this->k,($this->h-($y-$up/1000*$this->FontSize))*$this->k,$w*$this->k,-$ut/1000*$this->FontSizePt); }
protected function _parsejpg($file) { $a = getimagesize($file); if(!$a) $this->Error('Missing or incorrect image file: '.$file); if($a[2]!=2) $this->Error('Not a JPEG file: '.$file); if(!isset($a['channels']) || $a['channels']==3) $colspace = 'DeviceRGB'; elseif($a['channels']==4) $colspace = 'DeviceCMYK'; else $colspace = 'DeviceGray'; $bpc = isset($a['bits']) ? $a['bits'] : 8; $data = file_get_contents($file); return array('w'=>$a[0], 'h'=>$a[1], 'cs'=>$colspace, 'bpc'=>$bpc, 'f'=>'DCTDecode', 'data'=>$data); }
protected function _parsepng($file) { $f = fopen($file,'rb'); if(!$f) $this->Error('Can\'t open image file: '.$file); $info = $this->_parsepngstream($f,$file); fclose($f); return $info; }
protected function _parsepngstream($f, $file) { if($this->_readstream($f,8)!=chr(137).'PNG'.chr(13).chr(10).chr(26).chr(10)) $this->Error('Not a PNG file: '.$file); $this->_readstream($f,4); if($this->_readstream($f,4)!='IHDR') $this->Error('Incorrect PNG file: '.$file); $w = $this->_readint($f); $h = $this->_readint($f); $bpc = ord($this->_readstream($f,1)); if($bpc>8) $this->Error('16-bit depth not supported: '.$file); $ct = ord($this->_readstream($f,1)); if($ct==0 || $ct==4) $colspace = 'DeviceGray'; elseif($ct==2 || $ct==6) $colspace = 'DeviceRGB'; elseif($ct==3) $colspace = 'Indexed'; else $this->Error('Unknown color type: '.$file); if(ord($this->_readstream($f,1))!=0) $this->Error('Unknown compression method: '.$file); if(ord($this->_readstream($f,1))!=0) $this->Error('Unknown filter method: '.$file); if(ord($this->_readstream($f,1))!=0) $this->Error('Interlacing not supported: '.$file); $this->_readstream($f,4); $dp = '/Predictor 15 /Colors '.($colspace=='DeviceRGB' ? 3 : 1).' /BitsPerComponent '.$bpc.' /Columns '.$w; $pal = ''; $trns = ''; $data = ''; do { $n = $this->_readint($f); $type = $this->_readstream($f,4); if($type=='PLTE') { $pal = $this->_readstream($f,$n); $this->_readstream($f,4); } elseif($type=='tRNS') { $t = $this->_readstream($f,$n); if($ct==0) $trns = array(ord(substr($t,1,1))); elseif($ct==2) $trns = array(ord(substr($t,1,1)), ord(substr($t,3,1)), ord(substr($t,5,1))); else { $pos = strpos($t,chr(0)); if($pos!==false) $trns = array($pos); } $this->_readstream($f,4); } elseif($type=='IDAT') { $data .= $this->_readstream($f,$n); $this->_readstream($f,4); } elseif($type=='IEND') break; else $this->_readstream($f,$n+4); } while($n); if($colspace=='Indexed' && empty($pal)) $this->Error('Missing palette in '.$file); $info = array('w'=>$w, 'h'=>$h, 'cs'=>$colspace, 'bpc'=>$bpc, 'f'=>'FlateDecode', 'dp'=>$dp, 'pal'=>$pal, 'trns'=>$trns); if($ct>=4) { if(!function_exists('gzuncompress')) $this->Error('Zlib not available, can\'t handle alpha channel: '.$file); $data = gzuncompress($data); $color = ''; $alpha = ''; if($ct==4) { $len = 2*$w; for($i=0;$i<$h;$i++) { $pos = (1+$len)*$i; $color .= $data[$pos]; $alpha .= $data[$pos]; $line = substr($data,$pos+1,$len); $color .= preg_replace('/(.)./s','$1',$line); $alpha .= preg_replace('/.(.)/s','$1',$line); } } else { $len = 4*$w; for($i=0;$i<$h;$i++) { $pos = (1+$len)*$i; $color .= $data[$pos]; $alpha .= $data[$pos]; $line = substr($data,$pos+1,$len); $color .= preg_replace('/(.{3})./s','$1',$line); $alpha .= preg_replace('/.{3}(.)/s','$1',$line); } } unset($data); $data = gzcompress($color); $info['smask'] = gzcompress($alpha); $this->PDFVersion = '1.4'; } $info['data'] = $data; return $info; }
protected function _readstream($f, $n) { $res = ''; while($n>0 && !feof($f)) { $s = fread($f,$n); if($s===false) $this->Error('Error while reading stream'); $n -= strlen($s); $res .= $s; } if($n>0) $this->Error('Unexpected end of stream'); return $res; }
protected function _readint($f) { $a = unpack('Ni',$this->_readstream($f,4)); return $a['i']; }
protected function _parsegif($file) { if(!function_exists('imagepng')) $this->Error('GD extension is required for GIF support'); if(!function_exists('imagecreatefromgif')) $this->Error('GD has no GIF read support'); $im = imagecreatefromgif($file); if(!$im) $this->Error('Missing or incorrect image file: '.$file); imageinterlace($im,0); ob_start(); imagepng($im); $data = ob_get_clean(); imagedestroy($im); $f = fopen('php://temp','rb+'); if(!$f) $this->Error('Unable to create memory stream'); fwrite($f,$data); rewind($f); $info = $this->_parsepngstream($f,$file); fclose($f); return $info; }
protected function _out($s) { if($this->state==2) $this->pages[$this->page] .= $s."\n"; elseif($this->state==1) $this->buffer .= $s."\n"; elseif($this->state==0) $this->Error('No page has been added yet'); elseif($this->state==3) $this->Error('The document is closed'); }
protected function _putpages() { $nb = $this->page; for($n=1;$n<=$nb;$n++) { $this->PageInfo[$n]['n'] = $this->n+1+2*($n-1); } $kids = ''; for($n=1;$n<=$nb;$n++) $kids .= $this->PageInfo[$n]['n'].' 0 R '; $this->_newobj(); $this->_out('<</Type /Pages'); $this->_out('/Kids ['.$kids.']'); $this->_out('/Count '.$nb); $this->_out('>>'); $this->_out('endobj'); for($n=1;$n<=$nb;$n++) { $this->_putpage($n); } }
protected function _putpage($n) { $this->_newobj(); $this->_out('<</Type /Page'); $this->_out('/Parent 1 0 R'); if(isset($this->PageInfo[$n]['size'])) $this->_out(sprintf('/MediaBox [0 0 %.2F %.2F]',$this->PageInfo[$n]['size'][0],$this->PageInfo[$n]['size'][1])); if(isset($this->PageInfo[$n]['rotation'])) $this->_out('/Rotate '.$this->PageInfo[$n]['rotation']); $this->_out('/Resources 2 0 R'); if(!empty($this->PageLinks[$n])) { $annots = '/Annots ['; foreach($this->PageLinks[$n] as $pl) { $rect = sprintf('%.2F %.2F %.2F %.2F',$pl[0],$pl[1],$pl[0]+$pl[2],$pl[1]-$pl[3]); $annots .= '<</Type /Annot /Subtype /Link /Rect ['.$rect.'] /Border [0 0 0] '; if(is_string($pl[4])) $annots .= '/A <</S /URI /URI '.$this->_textstring($pl[4]).'>>>>'; else { $l = $this->links[$pl[4]]; if(isset($this->PageInfo[$l[0]]['size'])) $h = $this->PageInfo[$l[0]]['size'][1]; else $h = $this->hPt; $annots .= sprintf('/Dest [%d 0 R /XYZ 0 %.2F null]>>',$this->PageInfo[$l[0]]['n'],$h-$l[1]*$this->k); } } $this->_out($annots.']'); } if($this->WithAlpha) $this->_out('/Group <</Type /Group /S /Transparency /CS /DeviceRGB>>'); $this->_out('/Contents '.($this->n+1).' 0 R>>'); $this->_out('endobj'); $p = $this->pages[$n]; if($this->compress) { $p = gzcompress($p); $this->_newobj(); $this->_out('<<'); $this->_out('/Filter /FlateDecode'); $this->_out('/Length '.strlen($p).'>>'); $this->_putstream($p); $this->_out('endobj'); } else { $this->_newobj(); $this->_out('<</Length '.strlen($p).'>>'); $this->_putstream($p); $this->_out('endobj'); } }
protected function _putfonts() { foreach($this->FontFiles as $file=>$info) { $this->_newobj(); $this->FontFiles[$file]['n'] = $this->n; $font = file_get_contents($this->fontpath.$file); if(!$font) $this->Error('Font file not found: '.$file); if(substr($file,-2)=='.z') $font = gzuncompress($font); $this->_out('<</Length '.strlen($font)); if(substr($file,-2)=='.z') $this->_out('/Filter /FlateDecode'); $this->_out('/Length1 '.$info['length1']); if(isset($info['length2'])) $this->_out('/Length2 '.$info['length2'].' /Length3 0'); $this->_out('>>'); $this->_putstream($font); $this->_out('endobj'); } foreach($this->fonts as $k=>$font) { if(isset($font['type']) && $font['type']=='Core') { $this->fonts[$k]['n'] = $this->n+1; $this->_newobj(); $this->_out('<</Type /Font'); $this->_out('/BaseFont /'.$font['name']); $this->_out('/Subtype /Type1'); if($font['name']!='Symbol' && $font['name']!='ZapfDingbats') $this->_out('/Encoding /WinAnsiEncoding'); $this->_out('>>'); $this->_out('endobj'); } elseif(isset($font['type']) && $font['type']=='Type1') { $this->fonts[$k]['n'] = $this->n+1; $this->_newobj(); $this->_out('<</Type /Font'); $this->_out('/BaseFont /'.$font['name']); $this->_out('/Subtype /Type1'); $this->_out('/FirstChar 32 /LastChar 255'); $this->_out('/Widths '.($this->n+1).' 0 R'); $this->_out('/FontDescriptor '.($this->n+2).' 0 R'); $this->_out('/Encoding /WinAnsiEncoding'); $this->_out('>>'); $this->_out('endobj'); $this->_newobj(); $s = '['; for($i=32;$i<=255;$i++) $s .= $font['cw'][chr($i)].' '; $this->_out($s.']'); $this->_out('endobj'); $this->_newobj(); $s = '<</Type /FontDescriptor /FontName /'.$font['name']; foreach($font['desc'] as $k2=>$v) $s .= ' /'.$k2.' '.$v; if(!empty($font['file'])) $s .= ' /FontFile '.($this->FontFiles[$font['file']]['n']).' 0 R'; $this->_out($s.'>>'); $this->_out('endobj'); } } }
protected function _putimages() { foreach(array_keys($this->images) as $file) { $this->_putimage($this->images[$file]); unset($this->images[$file]['data']); unset($this->images[$file]['smask']); } }
protected function _putimage(&$info) { $this->_newobj(); $info['n'] = $this->n; $this->_out('<</Type /XObject'); $this->_out('/Subtype /Image'); $this->_out('/Width '.$info['w']); $this->_out('/Height '.$info['h']); if($info['cs']=='Indexed') $this->_out('/ColorSpace [/Indexed /DeviceRGB '.(strlen($info['pal'])/3-1).' '.($this->n+1).' 0 R]'); else { $this->_out('/ColorSpace /'.$info['cs']); if($info['cs']=='DeviceCMYK') $this->_out('/Decode [1 0 1 0 1 0 1 0]'); } $this->_out('/BitsPerComponent '.$info['bpc']); if(isset($info['f'])) $this->_out('/Filter /'.$info['f']); if(isset($info['dp'])) $this->_out('/DecodeParms <<'.$info['dp'].'>>'); if(isset($info['trns']) && is_array($info['trns'])) { $trns = ''; for($i=0;$i<count($info['trns']);$i++) $trns .= $info['trns'][$i].' '.$info['trns'][$i].' '; $this->_out('/Mask ['.$trns.']'); } if(isset($info['smask'])) $this->_out('/SMask '.($this->n+1).' 0 R'); $this->_out('/Length '.strlen($info['data']).'>>'); $this->_putstream($info['data']); $this->_out('endobj'); if(isset($info['smask'])) { $dp = '/Predictor 15 /Colors 1 /BitsPerComponent 8 /Columns '.$info['w']; $smask = array('w'=>$info['w'], 'h'=>$info['h'], 'cs'=>'DeviceGray', 'bpc'=>8, 'f'=>$info['f'], 'dp'=>$dp, 'data'=>$info['smask']); $this->_putimage($smask); } if($info['cs']=='Indexed') { $this->_newobj(); $pal = ($this->compress) ? gzcompress($info['pal']) : $info['pal']; $this->_out('<<'); if($this->compress) $this->_out('/Filter /FlateDecode'); $this->_out('/Length '.strlen($pal).'>>'); $this->_putstream($pal); $this->_out('endobj'); } }
protected function _putxobjectdict() { foreach($this->images as $image) $this->_out('/I'.$image['i'].' '.$image['n'].' 0 R'); }
protected function _putresourcedict() { $this->_out('/ProcSet [/PDF /Text /ImageB /ImageC /ImageI]'); $this->_out('/Font <<'); foreach($this->fonts as $font) $this->_out('/F'.$font['i'].' '.$font['n'].' 0 R'); $this->_out('>>'); $this->_out('/XObject <<'); $this->_putxobjectdict(); $this->_out('>>'); }
protected function _putresources() { $this->_putfonts(); $this->_putimages(); $this->_newobj(2); $this->_out('<<'); $this->_putresourcedict(); $this->_out('>>'); $this->_out('endobj'); }
protected function _putinfo() { $this->metadata['CreationDate'] = 'D:'.$this->CreationDate; foreach($this->metadata as $key=>$value) $this->_out('/'.$key.' '.$this->_textstring($value)); }
protected function _putcatalog() { $n = $this->PageInfo[1]['n']; $this->_out('/Type /Catalog'); $this->_out('/Pages 1 0 R'); if($this->ZoomMode=='fullpage') $this->_out('/OpenAction ['.$n.' 0 R /Fit]'); elseif($this->ZoomMode=='fullwidth') $this->_out('/OpenAction ['.$n.' 0 R /FitH null]'); elseif($this->ZoomMode=='real') $this->_out('/OpenAction ['.$n.' 0 R /XYZ null null 1]'); elseif(!is_string($this->ZoomMode)) $this->_out('/OpenAction ['.$n.' 0 R /XYZ null null '.sprintf('%.2F',$this->ZoomMode/100).']'); if($this->LayoutMode=='single') $this->_out('/PageLayout /SinglePage'); elseif($this->LayoutMode=='continuous') $this->_out('/PageLayout /OneColumn'); elseif($this->LayoutMode=='two') $this->_out('/PageLayout /TwoColumnLeft'); }
protected function _putheader() { $this->_out('%PDF-'.$this->PDFVersion); }
protected function _puttrailer() { $this->_out('/Size '.($this->n+1)); $this->_out('/Root '.$this->n.' 0 R'); $this->_out('/Info '.($this->n-1).' 0 R'); }
protected function _enddoc() { $this->_putheader(); $this->_putpages(); $this->_putresources(); $this->_newobj(); $this->_out('<<'); $this->_putinfo(); $this->_out('>>'); $this->_out('endobj'); $this->_newobj(); $this->_out('<<'); $this->_putcatalog(); $this->_out('>>'); $this->_out('endobj'); $o = strlen($this->buffer); $this->_out('xref'); $this->_out('0 '.($this->n+1)); $this->_out('0000000000 65535 f '); for($i=1;$i<=$this->n;$i++) $this->_out(sprintf('%010d 00000 n ',$this->offsets[$i])); $this->_out('trailer'); $this->_out('<<'); $this->_puttrailer(); $this->_out('>>'); $this->_out('startxref'); $this->_out($o); $this->_out('%%EOF'); $this->state = 3; }
protected function _newobj($n=null) { if($n===null) $n = ++$this->n; $this->offsets[$n] = strlen($this->buffer); $this->_out($n.' 0 obj'); }
protected function _putstream($data) { $this->_out('stream'); $this->_out($data); $this->_out('endstream'); }
protected function _httpencode($param, $value, $isUTF8) { if($isUTF8) $value = urlencode($value); if(PHP_SAPI=='cli') return $param."=\"$value\""; return $param."*=UTF-8''$value"; }
}
?>
