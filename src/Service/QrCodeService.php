<?php

namespace App\Service;

class QrCodeService
{
    /**
     * Génère une URL pour un QR code à partir d'un texte
     * Utilise un service en ligne (QR Server API) pour éviter d'installer des dépendances supplémentaires
     * 
     * @param string $data Le texte à encoder dans le QR code
     * @param int $size La taille du QR code en pixels
     * @return string L'URL vers l'image du QR code
     */
    public function generateQrCodeUrl(string $data, int $size = 150): string
    {
        // Utilise le service en ligne QR Server pour générer le QR code
        return 'https://api.qrserver.com/v1/create-qr-code/?size=' . $size . 'x' . $size . '&data=' . urlencode($data);
    }
} 