import { useState, useEffect } from 'react'
import ReactModal from 'react-modal';

import * as Core from '@coreui/react';

function Modal(props) {

  const [value, setValue] = useState(props.value)

  const onValueChanged = (e) => {
    const val = e.target.value;
    setValue(val);
  }

  const onKeyDown = (e) => {
     if (e.key === 'Enter') {
      props.onSaveHandler(e);
    }
    if (e.key === 'Escape') {
      props.onCloseHandler(e);
    }
  }

  return (
      <ReactModal
        isOpen = { props.currentState }
        ariaHideApp = { false }
        role = {`dialog`}
      >

        <div className="modal-dialog">
          <div className="modal-content">

            <div className="modal-header mb-4">
              <h5 className="modal-title">{ props.name }</h5>
              <button type="button" className="btn-close"  aria-label="Close" onClick={ props.onCloseHandler } ></button>
            </div>

            <div className="modal-body mb-4">
              <div className="input-group input-group-lg mb-2">
                <span className="input-group-text" id="inputGroup-sizing-lg"></span>

                  <input
                    autoFocus
                    type = { props.type }
                    className = "form-control"
                    aria-label = "Sizing example input"
                    aria-describedby = "inputGroup-sizing-lg"
                    r_data = { value }
                    r_type = { props.type }
                    r_key = { props.rkey }
                    onChange = { onValueChanged }
                    onKeyDown = { onKeyDown }
                  />

              </div>
              <div className="p-2 pt-4 mt-2 border">
              {
                props.type == 'file' && props.rkey == 'img' &&
                <img src = { props.value } className="w-25 mt-2" />
              }
              {
                props.type == 'text' &&
                <p>{ props.value }</p>
              }
              </div>

            </div>

            <div className="modal-footer">
              <button type="button" className="btn btn-secondary" data-bs-dismiss="modal" onClick = { props.onCloseHandler } >Close</button>
              <button
                type="button"
                className="btn btn-primary"
                r_type = { props.type }
                r_key = { props.rkey }
                r_data = { value }
                onClick = { props.onSaveHandler }
              >
                Save changes
              </button>
            </div>

          </div>
        </div>
     </ReactModal>
    )
}

export default Modal;

/**/
