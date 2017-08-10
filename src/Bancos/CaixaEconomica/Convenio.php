<?php
/*
 * The MIT License
 *
 * Copyright 2013 italo.
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */

namespace Umbrella\YaBoleto\Bancos\CaixaEconomica;

use ArrayObject;
use Umbrella\YaBoleto\AbstractConvenio;
use Umbrella\YaBoleto\Type\Number;
use Umbrella\YaBoleto\Type\StringBuilder;

/**
 * Classe que representa o convênio da Caixa Econômica.
 * 
 * @author  Edmo Farias <edmofarias@gmail.com>
 * @package YaBoleto
 */
class Convenio extends AbstractConvenio
{
    /**
     * Gera o campo livre do código de barras.
     * 
     * @param  ArrayObject $data
     * @return $data
     */
    public function gerarCampoLivre(ArrayObject $data)
    {
        $this->alterarTamanho('NossoNumero', 17);
        $this->alterarTamanho('CodigoCedente', 7); // 06 digitos + DV
        $this->alterarTamanho('CampoLivre', 18);   // 17 digitos + DV

        $data['CodigoCedente'] .= Number::modulo11($data['CodigoCedente'], 0, 0, false);

        $nossoNumero = StringBuilder::normalize($data['NossoNumero'], 17);

        $constante1 = '2'; // 1ª posição do Nosso Numero: Tipo de Cobrança (1-Registrada / 2-Sem Registro)
        $constante2 = '4'; // 2ª posição do Nosso Número: Identificador da Emissão do Boleto (4-Beneficiário)
        $sequencia1 = (String) substr($nossoNumero, 2, 3);
        $sequencia2 = (String) substr($nossoNumero, 5, 3);
        $sequencia3 = (String) substr($nossoNumero, 8, 9);

        if ($data['Carteira'] == 'RG') {
            $constante1 = '1'; // 1ª posição do Nosso Numero: Tipo de Cobrança (1-Registrada / 2-Sem Registro)
        }

        $data['CampoLivre'] = $sequencia1 . $constante1 . $sequencia2 . $constante2 . $sequencia3;

        // Calculando o DV do campo livre
        $campoLivre = $data['CodigoCedente'] . $data['CampoLivre'];
        $data['CampoLivre'] .= Number::modulo11($campoLivre, 0, 0, false);

        $this->layout = ':Banco:Moeda:FatorVencimento:Valor:CodigoCedente:CampoLivre';
    }

    /**
     * Ajusta o Nosso Numero antes de seta-lo no objeto Convenio.
     *
     * @param ArrayObject $data
     * @return mixed
     */
    public function ajustarNossoNumero(ArrayObject $data)
    {
        return $data['NossoNumero'];
    }
}
