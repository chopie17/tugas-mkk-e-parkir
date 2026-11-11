import { renderSVG } from 'uqr'

// Code 128B pattern table (107 patterns)
const CODE128_PATTERNS = [
  '212222', '222122', '222221', '121223', '121322', '131222', '122213', '122312', '132212', '221213',
  '221312', '231212', '112232', '122132', '122231', '113222', '123122', '123221', '223211', '221132',
  '221231', '213212', '223112', '312131', '311222', '321122', '321221', '312212', '322112', '322211',
  '212123', '212321', '232121', '111323', '131123', '131321', '112313', '132113', '132311', '211313',
  '231113', '231311', '112133', '112331', '132131', '113123', '113321', '133121', '313121', '211331',
  '231131', '213113', '213311', '213131', '311123', '311321', '331121', '312113', '312311', '332111',
  '314111', '221411', '431111', '111224', '111422', '121124', '121421', '141122', '141221', '112214',
  '112412', '122114', '122411', '142112', '142211', '241211', '221114', '413111', '241112', '134111',
  '111242', '121142', '121241', '114212', '124112', '124211', '411212', '421112', '421211', '212141',
  '214121', '412121', '111143', '111341', '131141', '114113', '114311', '411113', '411311', '113141',
  '114131', '311141', '411131', '211412', '211214', '211232', '2331112',
]

/**
 * Generate 1D Code128 Barcode as an SVG string.
 * Completely scannable by 1D laser & CCD barcode scanners.
 */
export function generateBarcodeSVG(text: string, height = 55): string {
  const clean = text.trim()
  const codes = [104] // Start Code B
  let checksum = 104

  for (let i = 0; i < clean.length; i++) {
    const charCode = clean.charCodeAt(i) - 32
    if (charCode >= 0 && charCode <= 95) {
      codes.push(charCode)
      checksum += charCode * (i + 1)
    }
  }

  codes.push(checksum % 103)
  codes.push(106) // Stop code

  let modules = ''
  for (const c of codes) {
    const pattern = CODE128_PATTERNS[c]
    if (!pattern) continue
    for (let p = 0; p < pattern.length; p++) {
      const width = parseInt(pattern[p], 10)
      const isBar = p % 2 === 0
      modules += (isBar ? '1' : '0').repeat(width)
    }
  }

  // Quiet zones (10 modules on each side)
  const quiet = '0'.repeat(10)
  const full = quiet + modules + quiet

  let rects = ''
  for (let i = 0; i < full.length; i++) {
    if (full[i] === '1') {
      rects += `<rect x="${i * 2}" y="0" width="2" height="${height}" fill="#0f172a"/>`
    }
  }

  const totalWidth = full.length * 2
  return `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 ${totalWidth} ${height}" preserveAspectRatio="none" style="width: 100%; height: 100%;">${rects}</svg>`
}

/**
 * Generate 2D QR Code as an SVG string using uqr.
 * Completely scannable by smartphone cameras and 2D barcode scanners.
 */
export function generateQRCodeSVG(text: string, border = 2): string {
  return renderSVG(text.trim(), { border })
}

