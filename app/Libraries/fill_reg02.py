#!/usr/bin/env python3
"""
fill_reg02.py  —  fills REG-02 blank PDF with form data + signature
Usage: python3 fill_reg02.py <json_data_file> <output_pdf_path>
JSON fields:
  data: { callsign, title, surname, forenames, ... }
  signature: "data:image/png;base64,..."  (optional)
"""

import sys, json, io, base64, os, tempfile
from pypdf import PdfReader, PdfWriter, generic
from reportlab.pdfgen import canvas
from PIL import Image

def main():
    if len(sys.argv) < 3:
        print("Usage: fill_reg02.py <json_file> <output_pdf>", file=sys.stderr)
        sys.exit(1)

    with open(sys.argv[1]) as f:
        payload = json.load(f)

    d         = payload.get('data', {})
    signature = payload.get('signature', None)
    blank_pdf = payload.get('blank_pdf')

    reader = PdfReader(blank_pdf)
    writer = PdfWriter()
    writer.append(reader)

    # ── Map form data to PDF field names ──────────────────────────────────
    dob = d.get('dob', '')
    if dob and '-' in dob:
        parts = dob.split('-')
        dob = f"{parts[2]}/{parts[1]}/{parts[0]}"  # dd/mm/yyyy

    # Build address string
    address = d.get('address', '').replace('\r\n', '\n').replace('\r', '\n')

    # Criminal record detail text
    def crim_text(key_yn, key_detail):
        if d.get(key_yn) == 'yes':
            return d.get(key_detail, '') or ''
        return ''

    text_fields = {
        'Callsign':                (d.get('callsign', '') or '').upper(),
        'Title':                   d.get('title', '') or '',
        'Surname':                 (d.get('surname', '') or '').upper(),
        'Forenames':               (d.get('forenames', '') or '').upper(),
        'Known As':                d.get('known_as', '') or '',
        'Date of Birth':           dob,
        'Home Telephone Number':   d.get('home_tel', '') or '',
        'Mobile Telephone Number': d.get('mobile', '') or '',
        'Nationality':             d.get('nationality', '') or '',
        'Former Nationality':      d.get('former_nationality', '') or '',
        'Place of Birth':          d.get('place_of_birth', '') or '',
        'Postal Address':          address,
        'Email Address':           d.get('email', '') or '',
        'Document A':              d.get('doc_a_type', '') or '',
        'Document A Date':         d.get('doc_a_date', '') or '',
        'Document A Reference':    d.get('doc_a_ref', '') or '',
        'Document B':              d.get('doc_b_type', '') or '',
        'Document B Date':         d.get('doc_b_date', '') or '',
        'Document B Reference':    d.get('doc_b_ref', '') or '',
        'Criminal 1':              crim_text('criminal_1', 'criminal_1_detail'),
        'Criminal 2':              crim_text('criminal_2', 'criminal_2_detail'),
        'Criminal 3':              crim_text('criminal_3', 'criminal_3_detail'),
        'Signature 1 Date':        __import__('datetime').date.today().strftime('%d/%m/%Y'),
    }

    # ── Checkbox states ────────────────────────────────────────────────────
    def yon(key):
        return '/Yes' if d.get(key) else '/Off'

    def crim_yn(key, is_yes_box):
        answered_yes = d.get(key) == 'yes'
        if is_yes_box:
            return '/Yes' if answered_yes else '/Off'
        else:
            return '/Yes' if not answered_yes else '/Off'

    checkbox_map = {
        'Home Tel':   '/Yes' if d.get('home_tel_ex') else '/Off',
        'Mobile Tel': '/Yes' if d.get('mobile_ex')   else '/Off',
        # Crim 1 = Yes box, Crim 2 = No box
        'Crim 1': crim_yn('criminal_1', True),
        'Crim 2': crim_yn('criminal_1', False),
        'Crim 3': crim_yn('criminal_2', True),
        'Crim 4': crim_yn('criminal_2', False),
        'Crim 5': crim_yn('criminal_3', True),
        'Crim 6': crim_yn('criminal_3', False),
        # Communications ticks
        'Tick 1':    yon('comms_national_email'),
        'Tick 2':    yon('comms_group_email'),
        'CheckBox1': yon('comms_national_tel'),
        'Tick 4':    yon('comms_group_tel'),
        'Tick 5':    yon('comms_national_sms'),
        'Tick 6':    yon('comms_group_sms'),
        'Tick 7':    yon('comms_national_post'),
        'Tick 8':    yon('comms_group_post'),
    }

    # ── Fill text fields ───────────────────────────────────────────────────
    for i in range(min(4, len(writer.pages))):
        writer.update_page_form_field_values(
            writer.pages[i], text_fields, auto_regenerate=False
        )

    # ── Fill checkboxes ────────────────────────────────────────────────────
    for page in writer.pages[:4]:
        for annot_ref in page.get('/Annots', []):
            annot = annot_ref.get_object()
            name  = str(annot.get('/T', ''))
            if name in checkbox_map:
                val = generic.NameObject(checkbox_map[name])
                annot.update({
                    generic.NameObject('/V'):  val,
                    generic.NameObject('/AS'): val,
                })

    # ── Overlay signature on page 3 ────────────────────────────────────────
    # Signature box: left column of signature row
    # Determined from field positions: Signature 1 Date rect=[478.1, 245.7, ...]
    # Full row from x≈27 to x≈574, y≈245.7 to y≈270.5
    if signature and signature.startswith('data:image/'):
        try:
            # Decode base64 image
            header, b64data = signature.split(',', 1)
            img_bytes = base64.b64decode(b64data)

            # Open with PIL — fix aspect ratio / normalise
            img = Image.open(io.BytesIO(img_bytes)).convert('RGBA')

            # Trim transparent whitespace around the actual signature
            bbox = img.getbbox()
            if bbox:
                img = img.crop(bbox)

            # Save to temp PNG
            tmp_sig = tempfile.NamedTemporaryFile(suffix='.png', delete=False)
            img.save(tmp_sig.name, 'PNG')
            tmp_sig.close()

            # PDF coords: signature box dimensions
            sig_x,  sig_y  = 150,  247    # bottom-left in PDF pts
            sig_w,  sig_h  = 325,  22     # width, height in pts

            # Scale to fit inside the box maintaining aspect ratio
            iw, ih = img.size
            scale  = min(sig_w / iw, sig_h / ih)
            draw_w = iw * scale
            draw_h = ih * scale
            # Centre vertically
            draw_y = sig_y + (sig_h - draw_h) / 2

            # Build overlay canvas at A4 size
            packet = io.BytesIO()
            c = canvas.Canvas(packet, pagesize=(595.3, 841.9))
            c.drawImage(
                tmp_sig.name,
                sig_x, draw_y,
                width=draw_w, height=draw_h,
                mask='auto', preserveAspectRatio=False,
            )
            c.save()
            packet.seek(0)

            os.unlink(tmp_sig.name)

            overlay = PdfReader(packet)
            writer.pages[2].merge_page(overlay.pages[0])

        except Exception as e:
            print(f"Warning: signature overlay failed: {e}", file=sys.stderr)

    # ── Write output ──────────────────────────────────────────────────────
    with open(sys.argv[2], 'wb') as out:
        writer.write(out)

    print("ok")

if __name__ == '__main__':
    main()
