<?

Function toIP($network){
        $iphex=gmp_strval($network,16);
        $l=strlen($iphex);   
        $oct0=substr($iphex,$l-2,2);
        $oct1=substr($iphex,$l-4,2);
        $oct2=substr($iphex,$l-6,2);
        #tricky since we may have 1 or 2 hex digits representing highest octect
        $oct3=substr($iphex,0,2-($l%2));
        $ip=hexdec($oct3).".".hexdec($oct2).".".hexdec($oct1).".".hexdec($oct0);
        return $ip;
}

Function toAddress($ip){
        $octets=split("\.",$ip);
        $oct0=gmp_mul($octets[0],gmp_pow(2,24));
        $oct1=gmp_mul($octets[1],gmp_pow(2,16));
        $address=gmp_add($oct0,$oct1);
                
        $oct2=gmp_mul($octets[2],gmp_pow(2,8));
        $address=gmp_add($address,$oct2);
        $oct3=gmp_init($octets[3]);
        $address=gmp_add($address,$oct3);        
        return $address;
}
Function getNetBits($mask){
        $hostbits=gmp_scan1($mask,0);
        $netsize=gmp_pow(2,$hostbits);
        $size=gmp_intval($netsize);
        #print "mask: $mask hostbits: $hostbits <br>";

        return 32-$hostbits;
}

?>
