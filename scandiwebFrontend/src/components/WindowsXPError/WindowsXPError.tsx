import { ReactElement } from 'react';
import styles from './WindowsXPError.module.css';

interface WindowsXPErrorProps {
  message?: string;
  onRetry?: () => void;
  onClose?: () => void;
}

export default function WindowsXPError({ message, onRetry, onClose }: WindowsXPErrorProps): ReactElement {
  return (
    <div className={styles.overlay}>
      <div className={styles.window}>
        <div className={styles.titleBar}>
          <div className={styles.titleLeft}>
            <div className={styles.titleIcon} />
            <span>Error</span>
          </div>
          <div className={styles.titleButtons}>
            <button className={styles.btnMin}>_</button>
            <button className={styles.btnMax}>□</button>
            <button className={`${styles.btnClose} ${styles.btnRed}`} onClick={onClose || (() => {})}>✕</button>
          </div>
        </div>
        <div className={styles.body}>
          <div className={styles.content}>
            <div className={styles.errorIcon} />
            <div>
              <p className={styles.message}>
                {message ?? 'Failed to load content. The application was unable to retrieve data from the server.'}
              </p>
              <p className={styles.code}>Error code: 0x800F0000</p>
            </div>
          </div>
          <div className={styles.actions}>
            {onRetry && (
              <button className={styles.okButton} onClick={onRetry}>
                Retry
              </button>
            )}
            {onClose && (
              <button className={styles.okButton} onClick={onClose}>
                OK
              </button>
            )}
          </div>
        </div>
      </div>
    </div>
  );
}
