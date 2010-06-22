<?php


class matchlinkerhelper extends Helper {
    var $html;

    function generate_opac_links($bhlplace, $ctrl_035, $ctrl_oclc, $ctrl_001) {


# BS - get bib id and select all 035s (or 001 if empty) and places for each id which matches from holdings table.
        # have logic for each institution which creates a canned link by id
        # return as an multi array with 1st element institution code, 2nd the canned url.

        #replace all tabs with spaces in ctrlnum first

        # first, see whether to use an 035, oclc or 001 dependent on whichever one exists first in the q
        if (strlen($ctrl_035) > 2) {
            $thecontrols = $ctrl_035;
            $thedelimiter = "_";
        }
        elseif (strlen($ctrl_oclc) > 2) {
            $thecontrols = $ctrl_oclc;
            $thedelimiter = "_";
        }
        elseif (strlen($ctrl_001) > 2) {
            $thecontrols = $ctrl_001;
            $thedelimiter = " ";
        }
        else {
            $thecontrols = NULL;
        }


        # may have a section here to override the above fields for certain sites. e.g if AMNH only can rely
        # on 001 to have a real control number (even if there is content in another field.

        # echo them all using direct url syntax as appropriate
        $html =''; // String to return
        // Initalise arrays for institutions control as some return more than one per match in 035 or oclc, delimited by underscore
        $controlnumbers = array();


        // Split multi-value database field on underscore into separate control numbers
        $controlnumbers  = split("_",$thecontrols);

        if (strlen($thecontrols) > 2) // only continue if element is greater than length two.
        {
            // Iterate through ctrl numbers...
            foreach ($controlnumbers as $controlnumber) {
                # Loop through each institution and create a direct url based on bhl_place

                switch ($bhlplace) {

                    // Check to see if it belongs to the Smithsonian...
                    case "SIL":
                        $html .= "<a class='actionbut' target='_blank' href=' ";
                        $html .= "http://siris-libraries.si.edu/ipac20/ipac.jsp?&menu=search&aspect=power&npp=20&ipp=20&profile=dial&ri=&oper=and&aspect=power&index=BIB&term=" . rtrim($controlnumber);
                        $html .=  "'>";
                        $html .=  "$bhlplace";
                        $html .=  "</a>";
                        break;

                    // Check to see if it belongs to the NHM...
                    case "NHM":
                        // only generate an url if Sirsi is not in $controlnumber
                        if (preg_match ("/\(Sirsi\)/", $controlnumber) == 0) {
                            $html .= "<a class='actionbut' target='_blank' href=' ";
                            $html .=  "http://unicorn.nhm.ac.uk/uhtbin/cgisirsi/x/0/0/5?searchdata1=" . rtrim($controlnumber)  . "&srchfield1=CKEY";
                            $html .=  "'>";
                            $html .=  "$bhlplace";
                            $html .=  "</a>";
                        }
                        break;

                    // Check to see if it belongs to the MBL  lib...
                    case "MBL":
                        $html .= "<a class='actionbut' target='_blank' href=' ";
                        $html .=  "http://cornelia.whoi.edu/cgi-bin/Pwebrecon.cgi?DB=local&SAB1=" . rtrim($controlnumber)  . "&BOOL1=as+a+phrase&FLD1=Keywords+anywhere+%28GKEY%29&CNT=10&HIST=1";
                        $html .=  "'>";
                        $html .=  "$bhlplace";
                        $html .=  "</a>";
                        break;

                    // Entry coming from Internet Archive
                    case "IA":
                        $matches = array();

                        if( preg_match( "/(BHLTID)(\d+)/", $controlnumber, $matches ) ) {
                            $html .= "<a class='actionbut' target='_blank' href=' ";
                            $html .=  "http://www.biodiversitylibrary.org/bibliography/" . intval($matches[2]);
                            $html .=  "'>";
                            $html .=  "BHL (" . intval($matches[2]) . ")";
                            $html .=  "</a>";
                        }
                        break;

                    // Add additional clauses for additional institutions here
                    default:
                        $html .=  "$bhlplace";
                        break;

                } // end switch

                $html .=  "<BR/>";
                break;
            } // End foreach loop
        }
        return $html;
    } // End function / method


    function generate_control($bhlplace, $ctrl_035) {


# BS - get bib id and select all 035s (or 001 if empty) and places for each id which matches from holdings table.
        # have logic for each institution which creates a canned link by id
        # return as an multi array with 1st element institution code, 2nd the canned url.

        #replace all tabs with spaces in ctrlnum first

        # first, see whether to use an 035, oclc or 001 dependent on whichever one exists first in the q
        if (strlen($ctrl_035) > 2) {
            $thecontrols = $ctrl_035;
            $thedelimiter = "_";
        }
        else {
            $thecontrols = NULL;
        }


        # may have a section here to override the above fields for certain sites. e.g if AMNH only can rely
        # on 001 to have a real control number (even if there is content in another field.

        # echo them all using direct url syntax as appropriate
        $html =''; // String to return
        // Initalise arrays for institutions control as some return more than one per match in 035 or oclc, delimited by underscore
        $controlnumbers = array();


        // Split multi-value database field on underscore into separate control numbers
        $controlnumbers  = split("_",$thecontrols);

        if (strlen($thecontrols) > 2) // only continue if element is greater than length two.
        {
            // Iterate through ctrl numbers...
            foreach ($controlnumbers as $controlnumber) {
                # Loop through each institution and create a direct url based on bhl_place

                switch ($bhlplace) {


                    case "NHM":

                    // Check to see if it belongs to the NHM...

                    // only generate an url if Sirsi is not in $controlnumber

                        if (preg_match ("/\(Sirsi\)/", $controlnumber) == 0) {
                            $controlnumber_withoutprefix = substr($controlnumber, 1, (strlen($controlnumber)-1) );
                            $html .=  rtrim($controlnumber_withoutprefix);
                        }
                        break;

                    default:

                        $html .=  "$ctrl_035";
                        $html .=  "<BR/>";


                } // end switch


            } // End foreach loop

        }

        return $html;
    } // End function / method



    
} // End class

?>
