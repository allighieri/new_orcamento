<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Compe;

class CompeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $banks = [
            ['code' => '001', 'bank_name' => 'Banco do Brasil S.A.'],
            ['code' => '003', 'bank_name' => 'Banco da Amazônia S.A.'],
            ['code' => '004', 'bank_name' => 'Banco do Nordeste do Brasil S.A.'],
            ['code' => '007', 'bank_name' => 'Banco Nacional de Desenvolvimento Econômico e Social (BNDES)'],
            ['code' => '012', 'bank_name' => 'Banco Inbursa S.A.'],
            ['code' => '021', 'bank_name' => 'Banco do Estado do Espírito Santo (Banestes)'],
            ['code' => '024', 'bank_name' => 'Banco de Pernambuco (BANDEPE)'],
            ['code' => '025', 'bank_name' => 'Banco Alfa S.A.'],
            ['code' => '029', 'bank_name' => 'Banco Itaú Consignado S.A.'],
            ['code' => '033', 'bank_name' => 'Banco Santander (Brasil) S.A.'],
            ['code' => '036', 'bank_name' => 'Banco Bradesco BBI S.A.'],
            ['code' => '037', 'bank_name' => 'Banco do Estado do Pará (Banpará)'],
            ['code' => '040', 'bank_name' => 'Banco Cargill S.A.'],
            ['code' => '041', 'bank_name' => 'Banrisul — Banco do Estado do Rio Grande do Sul'],
            ['code' => '047', 'bank_name' => 'Banese — Banco do Estado de Sergipe S.A.'],
            ['code' => '062', 'bank_name' => 'Hipercard Banco Múltiplo S.A.'],
            ['code' => '063', 'bank_name' => 'Bradescard S.A.'],
            ['code' => '064', 'bank_name' => 'Goldman Sachs do Brasil Banco Múltiplo S.A.'],
            ['code' => '065', 'bank_name' => 'Banco Andbank (Brasil) S.A.'],
            ['code' => '066', 'bank_name' => 'Banco Morgan Stanley S.A.'],
            ['code' => '069', 'bank_name' => 'Banco Crefisa S.A.'],
            ['code' => '070', 'bank_name' => 'BRB — Banco de Brasília S.A.'],
            ['code' => '074', 'bank_name' => 'Banco J. Safra S.A.'],
            ['code' => '075', 'bank_name' => 'Banco ABN AMRO S.A.'],
            ['code' => '076', 'bank_name' => 'Banco KDB do Brasil S.A.'],
            ['code' => '077', 'bank_name' => 'Banco Inter S.A.'],
            ['code' => '082', 'bank_name' => 'Banco Topázio S.A.'],
            ['code' => '083', 'bank_name' => 'Banco da China Brasil S.A.'],
            ['code' => '094', 'bank_name' => 'Banco Finaxis S.A.'],
            ['code' => '095', 'bank_name' => 'Banco Travelex S.A.'],
            ['code' => '096', 'bank_name' => 'Banco B3 S.A.'],
            ['code' => '102', 'bank_name' => 'XP Investimentos S.A.'],
            ['code' => '104', 'bank_name' => 'Caixa Econômica Federal'],
            ['code' => '107', 'bank_name' => 'BOCOM BBM S.A.'],
            ['code' => '117', 'bank_name' => 'Advanced Corretora de Câmbio Ltda'],
            ['code' => '120', 'bank_name' => 'Banco Rodobens S.A.'],
            ['code' => '121', 'bank_name' => 'Banco Agibank S.A.'],
            ['code' => '128', 'bank_name' => 'Braza Bank S.A.'],
            ['code' => '172', 'bank_name' => 'Albatross CCV S.A.'],
            ['code' => '184', 'bank_name' => 'Itaú BBA S.A.'],
            ['code' => '204', 'bank_name' => 'Bradesco Cartões S.A.'],
            ['code' => '208', 'bank_name' => 'BTG Pactual S.A.'],
            ['code' => '217', 'bank_name' => 'Banco John Deere S.A.'],
            ['code' => '222', 'bank_name' => 'Crédit Agricole Brasil S.A.'],
            ['code' => '233', 'bank_name' => 'Banco Cifra S.A.'],
            ['code' => '241', 'bank_name' => 'Banco Clássico'],
            ['code' => '254', 'bank_name' => 'Paraná Banco S.A.'],
            ['code' => '260', 'bank_name' => 'Nu Pagamentos S.A. (Nubank)'],
            ['code' => '265', 'bank_name' => 'Banco Fator S.A.'],
            ['code' => '269', 'bank_name' => 'HSBC Bank Brasil S.A.'],
            ['code' => '280', 'bank_name' => 'Avista S.A. Crédito'],
            ['code' => '299', 'bank_name' => 'Sorocred Crédito'],
            ['code' => '313', 'bank_name' => 'Amazônia Corretora de Câmbio Ltda'],
            ['code' => '323', 'bank_name' => 'Mercado Pago – Conta do Mercado Livre'],
            ['code' => '336', 'bank_name' => 'Banco C6 S.A.'],
            ['code' => '341', 'bank_name' => 'Itaú Unibanco S.A.'],
            ['code' => '349', 'bank_name' => 'AL5 S.A. Crédito'],
            ['code' => '367', 'bank_name' => 'Vitreo Distribuidora de Títulos e Valores Mobiliários S.A.'],
            ['code' => '366', 'bank_name' => 'Société Générale Brasil S.A.'],
            ['code' => '370', 'bank_name' => 'Banco Mizuho do Brasil S.A.'],
            ['code' => '376', 'bank_name' => 'Banco J. P. Morgan S.A.'],
            ['code' => '389', 'bank_name' => 'Banco Mercantil do Brasil S.A.'],
            ['code' => '394', 'bank_name' => 'Bradesco Financiamentos S.A.'],
            ['code' => '422', 'bank_name' => 'Banco Safra S.A.'],
            ['code' => '456', 'bank_name' => 'Banco MUFG Brasil S.A.'],
            ['code' => '464', 'bank_name' => 'Banco Sumitomo Mitsui Brasileiro S.A.'],
            ['code' => '473', 'bank_name' => 'Caixa Geral – Brasil S.A.'],
            ['code' => '479', 'bank_name' => 'ItaúBank S.A.'],
            ['code' => '487', 'bank_name' => 'Deutsche Bank S.A. – Banco Alemão'],
            ['code' => '505', 'bank_name' => 'Credit Suisse (Brasil) S.A.'],
            ['code' => '610', 'bank_name' => 'Banco VR S.A.'],
            ['code' => '611', 'bank_name' => 'Banco Paulista S.A.'],
            ['code' => '612', 'bank_name' => 'Banco Guanabara S.A.'],
            ['code' => '613', 'bank_name' => 'Omni Banco S.A.'],
            ['code' => '623', 'bank_name' => 'Banco Pan S.A.'],
            ['code' => '630', 'bank_name' => 'Letsbank / Smartbank S.A.'],
            ['code' => '633', 'bank_name' => 'Banco Rendimento S.A.'],
            ['code' => '634', 'bank_name' => 'Banco Triângulo S.A.'],
            ['code' => '643', 'bank_name' => 'Banco Pine S.A.'],
            ['code' => '654', 'bank_name' => 'Banco Digimais S.A.'],
            ['code' => '655', 'bank_name' => 'Banco Votorantim S.A.'],
            ['code' => '712', 'bank_name' => 'Banco Ourinvest S.A.'],
            ['code' => '741', 'bank_name' => 'Banco Ribeirão Preto'],
            ['code' => '743', 'bank_name' => 'Banco Semear S.A.'],
            ['code' => '746', 'bank_name' => 'Banco Modal S.A.'],
            ['code' => '747', 'bank_name' => 'Banco Rabobank International do Brasil S.A.'],
            ['code' => '751', 'bank_name' => 'Scotiabank Brasil S.A.'],
            ['code' => '755', 'bank_name' => 'Bank of America Merrill Lynch Banco Múltiplo S.A.'],
            ['code' => '756', 'bank_name' => 'Sicoob – Sistema de Cooperativas de Crédito'],
            ['code' => '757', 'bank_name' => 'Banco KEB Hana do Brasil S.A.'],
            ['code' => '748', 'bank_name' => 'Sicredi – Sistema de Crédito Cooperativo']
        ];
        
        foreach ($banks as $bank) {
            Compe::create([
                'code' => $bank['code'],
                'bank_name' => $bank['bank_name'],
                'active' => true
            ]);
        }
    }
}
