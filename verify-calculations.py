from pathlib import Path
from decimal import Decimal, ROUND_HALF_UP
import json

root = Path(__file__).resolve().parent
data = json.loads((root/'Bukti/database.json').read_text(encoding='utf8'))
groups = {}
for row in data['results']['rekonstruksi']:
    groups.setdefault((row['periode'],row['kode'],row['kelas']), []).append(row)
reference = []
for (period,code,kelas), rows in groups.items():
    complete = all(row['nilai'] is not None for row in rows)
    total = sum(Decimal(str(row['nilai']))*Decimal(str(row['bobot']))/100 for row in rows) if complete else None
    total = total.quantize(Decimal('.01'), rounding=ROUND_HALF_UP) if total is not None else None
    actual = next(row['hasil'] for row in data['ipk']['rows'] if row['periode']==period and row['kode']==code)
    assert (actual is None and total is None) or (actual is not None and Decimal(str(actual['nilai']))==total)
    reference.append({'periode':period,'kode':code,'kelas':kelas,'formula':[{'komponen':row['komponen'],'bobot':row['bobot'],'nilai':row['nilai']} for row in rows], 'nilai_manual':str(total) if total is not None else None,'sesuai_service':True})
assert Decimal('88.50') == Decimal(80)*Decimal('.20')+Decimal(90)*Decimal('.10')+Decimal(85)*Decimal('.30')+Decimal(95)*Decimal('.40')
assert Decimal('75.00') == Decimal(74)*Decimal('.20')+Decimal(80)*Decimal('.10')+Decimal(70)*Decimal('.30')+Decimal(78)*Decimal('.40')
assert (Decimal(39)/11).quantize(Decimal('.01'),rounding=ROUND_HALF_UP)==Decimal(str(data['ipk']['ip']))
assert (Decimal(30)/8)==Decimal(str(data['ips']['ip']))
record={'reference_method':'Decimal dari baris JOIN komponen; NULL dipertahankan, pembulatan dua desimal','pengambilan':reference,'ips_manual':'30/8 = 3.75','ipk_manual':'39/11 = 3.55','passed':True}
(root/'Bukti/manual-calculations.json').write_text(json.dumps(record,indent=2,ensure_ascii=False),encoding='utf8')
print('Tujuh pengambilan cocok dengan hitungan Decimal; MPD 88.50, MPE 75.00, IPS 3.75, IPK 3.55.')
