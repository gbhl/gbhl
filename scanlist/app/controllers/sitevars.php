<?
##############################################################################
#                                                                            #
#                             sitevars.php                                 #
#                                                                            #
##############################################################################
# PROGRAM : eDate                                                            #
# VERSION : 3.1b                                                             #
#                                                                            #
# NOTES   : site using default site layout and graphics                      #
##############################################################################
# All source code, images, programs, files included in this distribution     #
# Copyright (c) 2001-2005                                                    #
# Supplied by          : OnlyScript.info                                     #
##############################################################################
#                                                                            #
#    While we distribute the source code for our scripts and you are         #
#    allowed to edit them to better suit your needs, we do not               #
#    support modified code.  Please see the license prior to changing        #
#    anything. You must agree to the license terms before using this         #
#    software package or any code contained herein.                          #
#                                                                            #
#    Any redistribution without permission of OnlyScript.info                #
#    is strictly forbidden.                                                  #
#                                                                            #
##############################################################################
?>
<?

class globvars {
      var $maxoffers = 120;  // maximum live offers that a user can have at any one time
      var $brief_offers_heading = "List of catalogue records";
      var $add_cat_heading_1 = "Add new record to catalogue - part 1 of 2";
      var $add_cat_heading_2 = "Add new record to catalogue - part 2 of 2";
      var $update_cat_heading = "Update a catalogue record";
      var $fullview_offer_heading = "Full details";
      var $delete_offer_heading = "Delete a catalogue record";
      var $filepath = "F:\dig_lib";
      var $search_heading = "Search catalogue page";
      var $website = "Family History Database";
      var $register = "Register...";
      var $acdseedrive = "D:\\";
      var $acdseepath = "Program Files\\ACD Systems\\ACDSee\\5.0\\";
      var $acdexe = "ACDSee5.exe";
      var $nonimagefiletype = ".rtf .txt .doc .xls .pdf .xlr";
}

$globvars = new globvars;

?>