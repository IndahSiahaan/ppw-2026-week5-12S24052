    public function handle(Request $request): Response
    {
        $rawXml = trim($request->getContent());
        if (empty($rawXml)) {
            return $this->soapFault('soap:Client', 'Body XML SOAP tidak boleh kosong.');
        }

        libxml_use_internal_errors(true);
        $xml = simplexml_load_string($rawXml);
        if ($xml === false) {
            return $this->soapFault('soap:Client', 'Format XML tidak valid (Malformed XML).');
        }

        $nimNodes = $xml->xpath('//*[local-name()="CheckClearanceStatusRequest"]/*[local-name()="nim_peminjam"]');
        if (empty($nimNodes) || trim((string)$nimNodes[0]) === '') {
            return $this->soapFault('soap:Client', 'Elemen <nim_peminjam> tidak ditemukan atau kosong.');
        }

        $nim = trim((string)$nimNodes[0]);

        try {
            // Cek pinjaman aktif mahasiswa di tabel peminjamans
            $activeLoans = DB::table('peminjamans')
                ->where('nim_peminjam', $nim)
                ->where('status', 'dipinjam')
                ->count();
        } catch (\Exception $e) {
            $activeLoans = 0;
        }

        $isCleared = ($activeLoans === 0) ? 'true' : 'false';
        $safeNim = htmlspecialchars($nim, ENT_XML1, 'UTF-8');

        $responseXml = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<soap:Envelope xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/"
    xmlns:m="http://sipus.del.ac.id/soap/clearance">
    <soap:Header>
        <m:ProviderInfo>SIPUS-Del Legacy SOAP Clearance Engine v1.0</m:ProviderInfo>
    </soap:Header>
    <soap:Body>
        <m:CheckClearanceStatusResponse>
            <m:nim_peminjam>{$safeNim}</m:nim_peminjam>
            <m:active_loans>{$activeLoans}</m:active_loans>
            <m:is_cleared>{$isCleared}</m:is_cleared>
        </m:CheckClearanceStatusResponse>
    </soap:Body>
</soap:Envelope>
XML;
        return response($responseXml, 200)->header('Content-Type', 'text/xml; charset=utf-8');
    }