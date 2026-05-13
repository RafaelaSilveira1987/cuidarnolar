import React, { useState } from 'react';
import { RelatorioPlantion as RelatorioPlantonType, TurnoType } from '../types/plantao';
import TurnoCard from './TurnoCard';
import SinaisVitais from './SinaisVitais';
import TabelaMedicacoes from './TabelaMedicacoes';
import EvolucaoEnfermagem from './EvolucaoEnfermagem';
import Intercorrencias from './Intercorrencias';

interface RelatorioPlantonProps {
  relatorio: RelatorioPlantonType;
}

export default function RelatorioPlantion({ relatorio }: RelatorioPlantonProps) {
  const [selectedTurnoId, setSelectedTurnoId] = useState<string | null>(null);
  const [currentDate, setCurrentDate] = useState<Date>(relatorio.data);

  const selectedTurno = relatorio.turnos.find((t) => t.id === selectedTurnoId);
  const turnosGroupedByPaciente = {
    [relatorio.paciente.id]: relatorio.turnos,
  };

  const formatarData = (date: Date): string => {
    return new Intl.DateTimeFormat('pt-BR', {
      weekday: 'long',
      year: 'numeric',
      month: 'long',
      day: 'numeric',
    }).format(date);
  };

  const adicionarDia = (dias: number) => {
    const novaData = new Date(currentDate);
    novaData.setDate(novaData.getDate() + dias);
    setCurrentDate(novaData);
    setSelectedTurnoId(null);
  };

  return (
    <div className="min-h-screen bg-gray-50 p-4 md:p-6">
      <div className="max-w-6xl mx-auto">
        {/* Cabeçalho do Paciente */}
        <div className="bg-white rounded-lg p-6 mb-6 border border-gray-200">
          <div className="flex items-start justify-between gap-4 mb-6">
            <div className="flex items-start gap-4">
              <div className="w-16 h-16 rounded-full bg-gradient-to-br from-blue-400 to-blue-600 flex items-center justify-center text-white text-xl font-bold flex-shrink-0">
                {relatorio.paciente.iniciais}
              </div>
              <div>
                <h1 className="text-2xl font-bold text-gray-900">
                  {relatorio.paciente.nome}
                </h1>
                <div className="mt-2 space-y-1 text-sm text-gray-600">
                  <p>
                    <span className="font-semibold">Prontuário:</span> {relatorio.paciente.prontuario}
                  </p>
                  <p>
                    <span className="font-semibold">Idade:</span> {relatorio.paciente.idade} anos
                  </p>
                  <p>
                    <span className="font-semibold">Diagnóstico:</span> {relatorio.paciente.diagnostico}
                  </p>
                </div>
              </div>
            </div>
          </div>

          {/* Navegação de Data */}
          <div className="flex items-center justify-center gap-4">
            <button
              onClick={() => adicionarDia(-1)}
              className="p-2 hover:bg-gray-100 rounded-lg transition-colors"
              aria-label="Dia anterior"
            >
              <svg className="w-5 h-5 text-gray-600" fill="currentColor" viewBox="0 0 20 20">
                <path
                  fillRule="evenodd"
                  d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z"
                  clipRule="evenodd"
                />
              </svg>
            </button>
            <div className="text-center min-w-[300px]">
              <p className="text-sm text-gray-600 mb-1">Data do Relatório</p>
              <p className="text-lg font-semibold text-gray-900 capitalize">
                {formatarData(currentDate)}
              </p>
            </div>
            <button
              onClick={() => adicionarDia(1)}
              className="p-2 hover:bg-gray-100 rounded-lg transition-colors"
              aria-label="Próximo dia"
            >
              <svg className="w-5 h-5 text-gray-600" fill="currentColor" viewBox="0 0 20 20">
                <path
                  fillRule="evenodd"
                  d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z"
                  clipRule="evenodd"
                />
              </svg>
            </button>
          </div>
        </div>

        {/* Agrupamento por Paciente com Turnos */}
        {Object.entries(turnosGroupedByPaciente).map(([pacienteId, turnos]) => (
          <div key={pacienteId}>
            {/* Seleção de Turnos */}
            <div className="mb-6">
              <h2 className="text-lg font-semibold text-gray-900 mb-4">Turnos - {currentDate.toLocaleDateString('pt-BR')}</h2>
              <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
                {turnos.map((turno) => (
                  <TurnoCard
                    key={turno.id}
                    turno={turno}
                    isSelected={selectedTurnoId === turno.id}
                    onClick={() => setSelectedTurnoId(turno.id)}
                  />
                ))}
              </div>
            </div>

            {/* Detalhes do Turno Selecionado */}
            {selectedTurno && (
              <div className="bg-white rounded-lg p-6 border border-gray-200 space-y-6">
                <SinaisVitais sinais={selectedTurno.sinaisVitais} />
                <TabelaMedicacoes medicacoes={selectedTurno.medicacoes} />
                <EvolucaoEnfermagem evolucao={selectedTurno.evolucao} />
                <Intercorrencias intercorrencias={selectedTurno.intercorrencias} />

                {/* Rodapé do Relatório */}
                <div className="border-t border-gray-200 pt-6 mt-6">
                  <div className="flex items-center justify-between">
                    <div className="flex items-center gap-3">
                      <img
                        src={selectedTurno.plantonista.avatar}
                        alt={selectedTurno.plantonista.nome}
                        className="w-10 h-10 rounded-full"
                      />
                      <div>
                        <p className="text-sm font-semibold text-gray-900">
                          {selectedTurno.plantonista.nome}
                        </p>
                        <p className="text-xs text-gray-600">{selectedTurno.plantonista.coren}</p>
                      </div>
                    </div>
                    {selectedTurno.assinado ? (
                      <div className="flex items-center gap-2 px-4 py-2 bg-green-100 text-green-700 rounded-full text-sm font-semibold">
                        <svg className="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                          <path
                            fillRule="evenodd"
                            d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                            clipRule="evenodd"
                          />
                        </svg>
                        Relatório assinado
                      </div>
                    ) : (
                      <button className="px-4 py-2 bg-blue-600 text-white text-sm font-semibold rounded-lg hover:bg-blue-700 transition-colors">
                        Assinar relatório
                      </button>
                    )}
                  </div>
                </div>
              </div>
            )}

            {!selectedTurno && (
              <div className="bg-white rounded-lg p-12 border border-gray-200 text-center">
                <p className="text-gray-600 font-medium">
                  Selecione um turno para visualizar o relatório completo
                </p>
              </div>
            )}
          </div>
        ))}
      </div>
    </div>
  );
}
