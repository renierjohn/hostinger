import { useState, useEffect } from 'react'
import { DragDropContext,Droppable,Draggable } from 'react-beautiful-dnd';
import ReactModal from 'react-modal';
import Modal from './Modal';

function CardAdvance(props) {

  return (
    <div className="bg-light pt-5 pb-5 shadow-sm dnd">
      <div className="container">
        <div className="row pt-5">
          <div className="col-12 text-center">
            <h3 className="text-uppercase border-bottom mb-4">{ props.title }</h3>
            <p>{ props.title }</p>
          </div>
        </div>
      </div>
    </div>

  )
}

export default CardAdvance
