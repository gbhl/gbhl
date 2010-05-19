<?php

// this component takes a file handle of pure local control numbers and runs a series of checks. It returns a multi-dimensional array of four arrays, one for local ctrls not found, one for rejected local ctrls that have already been bid upon, one for accepted local ctrls and another containing the primary keys of accepted bids.


// NOTE FOR BERS - COMMENTED OUT LINES CONTAIN ADDITIONAL CODE TO COPE WITH extra holdings table - may need to be added in. Also dodgy logic on the 'or' statements. Pap. 

class BatchComponent extends Object {

    var $controller = true;

    //var $uses = array('Bid','Bib','Holding');
    var $returns = array(); // Final multi array
    
    var $rejected_bids = array(); // Those that have been bid upon
    var $rejected_notfound = array();
    var $accepted = array();
    var $accepted_bib_ids = array();


     function startup(&$controller)
    {
        // This method takes a reference to the controller which is loading it.
        // Perform controller initialization here.
    }

    
    function process($bids)    
    {
     for($line=fgets($bids); !feof($bids);$line=fgets($bids))
        {
        $line=trim($line);
        
        $rel_bib = $this->Bib->find("contains \('%$line%'\)",'matches');
    //HOLDINGS CODE $rel_holding = $this->Holding->find("$line",'localctrl');
        
     //HOLDINGS CODE if (!$rel_bib) or (!$rel_holding))   
        if (!$rel_bib) 
        {
          // Add them to bibs rejected - not found,   end for loop 
          array_push($rejected_notfound, $line);
         
        }
    //HOLDINGS CODE  elseif (($this->Bid->find("$rel_bib[id]",'bib_id'))or($this->Bid->find("$rel_holding[bib_id]",'bib_id')))    
        elseif ($this->Bid->find("$rel_bib[id]",'bib_id'))
        {
          // Add them to bibs rejected - bid already inplace,   end for loop     
          array_push($rejected_bids , $line);
            
        }    
        else
        {
            // Record the original local control numbers that can be bid upon 
            array_push($accepted, $line);
            
            // Also, return the PK bib ID's of  
            //HOLDINGS CODE  if (!$rel_bib) {$line = $rel_holding[bib_id];} else {$line = $rel_bib;}
            //HOLDINGS CODE $line = $rel_bib;
            array_push($accepted_bib_ids, $rel_bib);
        }    

        }// End for
      
                
    //add ctrls accepted + ctrlsreturned to $returns as mutlidemnsional array
        array_push($returns, $rejected_bids, $rejected_notfound, $accepted, $accepted_bib_ids);
        return $returns;

    }    // End func


}// End class

?>